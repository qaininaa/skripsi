<?php

namespace App\Services\Reports;

use App\Models\Analyst;
use App\Models\EnvSectionInstance;
use App\Models\PersonnelInstance;
use App\Models\PersonnelRow;
use App\Models\PersonnelSamplingEntry;
use App\Models\Report;
use App\Models\ReportEnvironmentalEntry;
use App\Models\ReportSectionColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ReportEntryService
 *
 * Menangani semua logika penyimpanan data isian laporan:
 *   1. Identitas instrument (Air Sampler)
 *   2. Identitas medium agar
 *   3. Data inkubasi (inkubator) dengan sistem ownership 3-grup
 *   4. header_data: metadata laporan dengan ownership per-field
 *   5. Analis yang terlibat + shift assignment
 *   6. Waktu paparan per lokasi (settle/swab/exposure) â€” fan-out ke entries
 *   7. Data CFU + waktu per_location langsung di baris tabel
 */
class ReportEntryService
{
    /**
     * Validasi nilai CFU dari input entries sebelum disimpan.
     * Nilai valid: '<1', 'TNTC', atau bilangan bulat positif (1, 5, 250, ...).
     *
     * @param  array $entries  Input entries bersarang 4 level dari request
     * @return array           Array path field yang tidak valid (kosong = semua valid)
     */
    public function validateCfu(array $entries): array
    {
        $cfuPattern = '/^(<1|TNTC|[1-9][0-9]*)$/i';
        $invalidFields = [];

        foreach ($entries as $sectionKey => $instanceMap) {
            if (! is_array($instanceMap)) {
                continue;
            }
            foreach ($instanceMap as $instanceKey => $periodMap) {
                if (! is_array($periodMap)) {
                    continue;
                }
                foreach ($periodMap as $periodKey => $shiftMap) {
                    if (! is_array($shiftMap)) {
                        continue;
                    }
                    foreach ($shiftMap as $shiftKey => $data) {
                        if (! is_array($data)) {
                            continue;
                        }
                        foreach (['cfu_bacteria', 'cfu_fungi'] as $field) {
                            $v = trim((string) ($data[$field] ?? ''));
                            if ($v !== '' && ! preg_match($cfuPattern, $v)) {
                                $invalidFields[] = "entries.{$sectionKey}.{$instanceKey}.{$periodKey}.{$shiftKey}.{$field}";
                            }
                        }
                    }
                }
            }
        }

        return $invalidFields;
    }

    /**
     * Migrasi format ownership lama (per-seksi) ke format baru (per-field).
     *
     * Format LAMA: header_data['_field_owners']['section_key'] = user_id
     * Format BARU: header_data['_field_owners']['section_key.field_key'] = user_id
     *
     * Dipanggil sekali saat laporan pertama kali dibuka setelah upgrade sistem.
     */
    public function migrateFieldOwners(Report $report): void
    {
        $hd = $report->header_data ?? [];
        $owners = $hd['_field_owners'] ?? [];
        if (empty($owners)) {
            return;
        }

        $changed = false;
        foreach (array_keys($owners) as $k) {
            if (! str_contains((string) $k, '.')) {
                $sectionData = $hd[$k] ?? null;
                if (is_array($sectionData) && ! empty($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (! isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                            $changed = true;
                        }
                    }
                    unset($owners[$k]);
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $hd['_field_owners'] = $owners;
            $report->header_data = $hd;
            $report->saveQuietly();
        }
    }

    /**
     * Normalisasi nilai CFU mentah dari input form ke string yang valid atau null.
     *
     * Nilai yang VALID: '<1', 'TNTC', bilangan bulat positif (mis: 1, 250).
     * Nilai yang TIDAK VALID: 0, negatif, desimal, string sembarang â†’ dikembalikan null.
     */
    public static function normalizeCfu(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $v = trim((string) $raw);
        if ($v === '') {
            return null;
        }
        if ($v === '<1') {
            return '<1';
        }
        if (strtoupper($v) === 'TNTC') {
            return 'TNTC';
        }
        if (preg_match('/^[1-9][0-9]*$/', $v)) {
            return $v;
        }

        return null;
    }

    /**
     * Simpan semua data form laporan ke database.
     *
     * Menangani 7 kelompok data secara berurutan (instrument, medium, incubator,
     * header_data, analis, waktu paparan, CFU).
     *
     * @param  Request $request  HTTP request berisi seluruh isian form
     * @param  Report  $report   Model laporan yang sedang diisi
     * @return array             $savedSectionIds: ['section_uuid|instance' => true]
     */
    public function process(Request $request, Report $report): array
    {
        $myShift = 1;

        //  1. IDENTITAS INSTRUMEN (Air Sampler) 
        $this->saveInstrument($request, $report);

        //  2. IDENTITAS MEDIUM AGAR 
        $this->saveMediums($request, $report);

        //  3. DATA INKUBASI 
        // Menyimpan ke DB dan mengembalikan header_data yang sudah diperbarui.
        $hd = $this->saveIncubators($request, $report);

        //  4 & 5. HEADER_DATA + ANALIS + SHIFT ASSIGNMENT 
        [$hd, $owners] = $this->saveHeaderData($request, $report, $hd);

        $this->saveAnalysts($request, $report);
        $this->saveSectionColumnNames($request, $report);

        $shiftAssignment = $request->input('shift_assignment', []);
        if (! empty($shiftAssignment)) {
            $existing = $hd['shift_assignments'] ?? [];
            foreach ($shiftAssignment as $secId => $cols) {
                $existing[$secId] = array_map('intval', $cols);
            }
            $hd['shift_assignments'] = $existing;
        }

        //  6. BANGUN PETA LOKASI DAN INSTANCE 
        $savedSectionIds = [];
        [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot] =
            $this->buildSectionLocationMaps($report);
        $instanceLookup = $this->buildInstanceLookup($report);

        //  7. WAKTU PAPARAN (FAN-OUT KE ENTRIES) 
        $settleTimes   = $request->input('settle_times',   []);
        $swabTimes     = $request->input('swab_times',     []);
        $exposureTimes = $request->input('exposure_times', []);

        [$savedSectionIds, $hd, $owners] = $this->saveSettleTimes(
            $settleTimes, $report, $sectionLocations, $instanceLookup,
            $savedSectionIds, $hd, $owners, $myShift
        );
        [$savedSectionIds, $hd, $owners] = $this->saveSwabTimes(
            $swabTimes, $report, $sectionLocations, $instanceLookup,
            $savedSectionIds, $hd, $owners, $myShift
        );
        [$savedSectionIds, $hd, $owners] = $this->saveExposureTimes(
            $exposureTimes, $report, $sectionLocations, $instanceLookup,
            $savedSectionIds, $hd, $owners, $myShift
        );

        // Simpan header_data jika ada blok 4, 5, atau 6 yang menghasilkan perubahan.
        if ($request->has('header_data') || ! empty($shiftAssignment)
            || ! empty($settleTimes) || ! empty($swabTimes) || ! empty($exposureTimes)) {
            $hd['_field_owners'] = $owners;
            $report->update(['header_data' => $hd]);
        }

        // Stamp timestamp terakhir kali analis ini menyimpan.
        $this->stampLastSaveTimestamp($report);

        // 7. CFU UPSERT + PER_LOCATION TIME 
        $savedSectionIds = $this->saveCfuEntries(
            $request, $report, $locationSectionType, $locationSectionId,
            $locationSectionTimeSlot, $instanceLookup, $savedSectionIds, $myShift
        );

        // 8. PEMANTAUAN PERSONEL 
        $hasPersonnelData = false;
        if ($request->has('personnel') || $request->has('page_notes')) {
            $hasPersonnelData = $this->savePersonnel($request, $report);
        }

        return [$savedSectionIds, $hasPersonnelData];
    }

    // Private helper methods

    private function saveInstrument(Request $request, Report $report): void
    {
        if (! $request->has('air_sampler')) {
            return;
        }
        $asData = $request->input('air_sampler', []);
        $report->instrumentIdentities()->updateOrCreate(
            ['tool_name' => $asData['tool_name'] ?? 'Air Sampler'],
            [
                'no_id'            => $asData['no_id']            ?? null ?: null,
                'calibration_date' => $asData['calibration_date'] ?? null ?: null,
                'due_date'         => $asData['due_date']         ?? null ?: null,
            ]
        );
    }

    private function saveMediums(Request $request, Report $report): void
    {
        if (! $request->has('medium')) {
            return;
        }
        $report->load('reportType.media');
        foreach ($request->input('medium', []) as $medKey => $data) {
            $medium = $report->reportType->media->firstWhere('name', $medKey);
            if ($medium) {
                $report->mediumIdentities()->updateOrCreate(
                    ['name' => $medKey],
                    [
                        'medium_id'       => $medium->id,
                        'batch_number'    => $data['batch_number']    ?? null ?: null,
                        'gpt_number'      => $data['gpt_number']      ?? null ?: null,
                        'expiration_date' => $data['expiration_date'] ?? null ?: null,
                    ]
                );
            }
        }
    }

    /**
     * Simpan data inkubasi dengan sistem ownership 3-grup (info/in/out).
     * Menyimpan langsung ke DB dan mengembalikan header_data yang sudah diperbarui.
     *
     * @return array  header_data setelah diperbarui ownership inkubator
     */
    private function saveIncubators(Request $request, Report $report): array
    {
        $freshHd   = $report->header_data ?? [];
        $inkOwners = $freshHd['_field_owners'] ?? [];

        if (! $request->has('incubator')) {
            return $freshHd;
        }

        foreach ($request->input('incubator', []) as $tempKey => $data) {
            $infoFields = array_filter([
                'no_id'                => $data['no_id']                ?? null ?: null,
                'calibration_date'     => $data['calibration_date']     ?? null ?: null,
                'due_date_calibration' => $data['due_date_calibration'] ?? null ?: null,
            ]);
            $inFields = array_filter([
                'incubated_by' => $data['incubated_by'] ?? null ?: null,
                'date_in'      => $data['date_in']      ?? null ?: null,
                'time_in'      => $data['time_in']      ?? null ?: null,
            ]);
            $outFields = array_filter([
                'removed_by' => $data['removed_by'] ?? null ?: null,
                'date_out'   => $data['date_out']   ?? null ?: null,
                'time_out'   => $data['time_out']   ?? null ?: null,
            ]);

            $ownerKeyInfo = "incubator_{$tempKey}_info";
            $ownerKeyIn   = "incubator_{$tempKey}_in";
            $ownerKeyOut  = "incubator_{$tempKey}_out";

            $infoLocked = isset($inkOwners[$ownerKeyInfo]) && (string) $inkOwners[$ownerKeyInfo] !== (string) Auth::id();
            if (! $infoLocked) {
                if (! empty($infoFields)) {
                    $inkOwners[$ownerKeyInfo] = (string) Auth::id();
                }
            } else {
                $infoFields = [];
            }

            $inLocked = isset($inkOwners[$ownerKeyIn]) && (string) $inkOwners[$ownerKeyIn] !== (string) Auth::id();
            if (! $inLocked) {
                if (! empty($inFields)) {
                    $inkOwners[$ownerKeyIn] = (string) Auth::id();
                }
            } else {
                $inFields = [];
            }

            $outLocked = isset($inkOwners[$ownerKeyOut]) && (string) $inkOwners[$ownerKeyOut] !== (string) Auth::id();
            if (! $outLocked) {
                if (! empty($outFields)) {
                    $inkOwners[$ownerKeyOut] = (string) Auth::id();
                }
            } else {
                $outFields = [];
            }

            $mergedData = array_merge($infoFields, $inFields, $outFields);
            if (! empty($mergedData)) {
                $report->incubators()->updateOrCreate(
                    ['report_type_incubator_id' => $tempKey],
                    $mergedData
                );
            }
        }

        $freshHd['_field_owners'] = $inkOwners;
        $report->update(['header_data' => $freshHd]);

        return $freshHd;
    }

    /**
     * Proses field-field header_data dengan sistem ownership per-field.
     *
     * @param  array $hd  header_data saat ini (sudah diperbarui oleh saveIncubators)
     * @return array       [$hd_baru, $owners_baru]
     */
    private function saveHeaderData(Request $request, Report $report, array $hd): array
    {
        $owners = $hd['_field_owners'] ?? [];

        // Migrasi inline format ownership lama â†’ baru (untuk data yang belum dimigrasi).
        foreach (array_keys($owners) as $k) {
            if (! str_contains((string) $k, '.')) {
                $sectionData = $hd[$k] ?? null;
                if (is_array($sectionData) && ! empty($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (! isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                        }
                    }
                    unset($owners[$k]);
                }
            }
        }

        if (! $request->has('header_data')) {
            return [$hd, $owners];
        }

        $incoming = $request->input('header_data');
        unset($incoming['_field_owners']);

        foreach ($incoming as $sectionKey => $sectionData) {
            if (! is_array($sectionData)) {
                $ownerKey = $sectionKey;
                if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                    continue;
                }
                if ($sectionData !== null && $sectionData !== '') {
                    $owners[$ownerKey] = (string) Auth::id();
                }
                $hd[$sectionKey] = $sectionData;
                continue;
            }

            foreach ($sectionData as $fieldKey => $fieldValue) {
                $ownerKey = "{$sectionKey}.{$fieldKey}";
                if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                    continue;
                }
                if ($fieldValue !== null && $fieldValue !== '') {
                    $owners[$ownerKey] = (string) Auth::id();
                }
                $hd[$sectionKey][$fieldKey] = $fieldValue;
            }
        }

        $hd['_field_owners'] = $owners;

        return [$hd, $owners];
    }

    private function saveAnalysts(Request $request, Report $report): void
    {
        if ($request->has('analyst_monitoring')) {
            $ids = array_values(array_filter((array) $request->input('analyst_monitoring')));
            foreach ($ids as $uid) {
                Analyst::updateOrCreate(
                    ['report_id' => $report->id, 'user_id' => $uid, 'type' => 'monitoring']
                );
            }
        }
        if ($request->has('analyst_reading')) {
            $ids = array_values(array_filter((array) $request->input('analyst_reading')));
            foreach ($ids as $uid) {
                Analyst::updateOrCreate(
                    ['report_id' => $report->id, 'user_id' => $uid, 'type' => 'reading']
                );
            }
        }
    }

    private function stampLastSaveTimestamp(Report $report): void
    {
        $freshHd = $report->fresh()->header_data ?? [];
        $tsKey   = $report->status === 'reading' ? 'ttd_reading_timestamps' : 'ttd_monitoring_timestamps';
        $freshHd[$tsKey][(string) Auth::id()] = now()->toDateTimeString();
        $report->update(['header_data' => $freshHd]);
    }

    /**
     * Bangun peta lokasi per seksi dan lookup table per pivot_id.
     *
     * @return array [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot]
     */
    public function buildSectionLocationMaps(Report $report): array
    {
        $sectionLocations     = [];
        $locationSectionType     = [];
        $locationSectionId       = [];
        $locationSectionTimeSlot = [];

        foreach ($report->reportType->sections()->with('locations.room')->get() as $_sec) {
            $sectionLocations[$_sec->id] = [];
            foreach ($_sec->locations as $_loc) {
                $locationId = (string) $_loc->id;
                $cls = strtolower($_loc->room->class ?? '');
                $num = $_loc->location_number ?? '';
                $sectionLocations[$_sec->id][] = [
                    'location_id'     => $locationId,
                    'class'           => $cls,
                    'location_number' => $num,
                ];
                $locationSectionType[$locationId]     = $_sec->measurement_type;
                $locationSectionId[$locationId]       = $_sec->id;
                $locationSectionTimeSlot[$locationId] = $_sec->time_slot_type;
            }
        }

        return [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot];
    }

    /**
     * Bangun lookup: $instanceLookup[location_id][instance_number] = env_section_instance_id.
     */
    public function buildInstanceLookup(Report $report): array
    {
        $instanceLookup = [];
        EnvSectionInstance::where('report_id', $report->id)
            ->orderByRaw('CASE WHEN parent_instance_id IS NULL THEN 0 ELSE 1 END, created_at, id')
            ->get()
            ->groupBy('location_id')
            ->each(function ($group, $locationId) use (&$instanceLookup) {
                foreach ($group->values() as $idx => $inst) {
                    $instanceLookup[(string) $locationId][$idx + 1] = (string) $inst->id;
                }
            });

        return $instanceLookup;
    }

    /**
     * Simpan settle times (seksi dual_ab: jam per kelas ruangan A/B) dan fan-out ke entries.
     *
     * @return array [$savedSectionIds, $hd, $owners]
     */
    private function saveSettleTimes(
        array $settleTimes, Report $report, array $sectionLocations, array $instanceLookup,
        array $savedSectionIds, array $hd, array $owners, int $myShift
    ): array {
        if (empty($settleTimes)) {
            return [$savedSectionIds, $hd, $owners];
        }

        foreach ($settleTimes as $secId => $instanceData) {
            if (! is_array($instanceData)) {
                continue;
            }
            foreach ($instanceData as $instNum => $data) {
                if (! is_array($data)) {
                    continue;
                }
                foreach ($data as $col => $abData) {
                    if (! is_array($abData)) {
                        continue;
                    }
                    $ownerKey = "settle_times_{$secId}_{$instNum}_{$col}";
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue;
                    }
                    $hasVal = collect($abData)->flatten()
                        ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if ($hasVal) {
                        $owners[$ownerKey] = (string) Auth::id();
                        $savedSectionIds["{$secId}|{$instNum}"] = true;
                        foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                            $ab        = $locInfo['class'];
                            $st        = $abData[$ab] ?? [];
                            $startTime = ($st['start_time'] ?? '') ?: null;
                            $endTime   = ($st['end_time']   ?? '') ?: null;
                            if ($startTime === null && $endTime === null) {
                                continue;
                            }
                            $instanceId = $instanceLookup[(string) $locInfo['location_id']][(int) $instNum] ?? null;
                            if (! $instanceId) {
                                continue;
                            }
                            ReportEnvironmentalEntry::updateOrCreate(
                                [
                                    'report_id'               => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number'           => (int) $col,
                                    'shift'                   => $myShift,
                                ],
                                ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                            );
                        }
                    }
                    $hd['settle_times'][$secId][$instNum][$col] = array_replace_recursive(
                        $hd['settle_times'][$secId][$instNum][$col] ?? [],
                        $abData
                    );
                }
            }
        }

        $hd['_field_owners'] = $owners;

        return [$savedSectionIds, $hd, $owners];
    }

    /**
     * Simpan swab times (seksi swab: jam per slot s1/s1_2/s1_3) dan fan-out ke entries.
     *
     * @return array [$savedSectionIds, $hd, $owners]
     */
    private function saveSwabTimes(
        array $swabTimes, Report $report, array $sectionLocations, array $instanceLookup,
        array $savedSectionIds, array $hd, array $owners, int $myShift
    ): array {
        if (empty($swabTimes)) {
            return [$savedSectionIds, $hd, $owners];
        }

        foreach ($swabTimes as $secId => $instanceData) {
            if (! is_array($instanceData)) {
                continue;
            }
            foreach ($instanceData as $instNum => $data) {
                if (! is_array($data)) {
                    continue;
                }
                foreach ($data as $col => $slotData) {
                    if (! is_array($slotData)) {
                        continue;
                    }
                    $ownerKey = "swab_times_{$secId}_{$instNum}_{$col}";
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue;
                    }
                    $hasVal = collect($slotData)->flatten()
                        ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if ($hasVal) {
                        $owners[$ownerKey] = (string) Auth::id();
                        $savedSectionIds["{$secId}|{$instNum}"] = true;
                        foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                            $locNum = $locInfo['location_number'];
                            if (stripos($locNum, 'S1-3') !== false) {
                                $swabKey = 's1_3';
                            } elseif (stripos($locNum, 'S1-2') !== false) {
                                $swabKey = 's1_2';
                            } else {
                                $swabKey = 's1';
                            }
                            $st        = $slotData[$swabKey] ?? [];
                            $startTime = ($st['mulai']   ?? '') ?: null;
                            $endTime   = ($st['selesai'] ?? '') ?: null;
                            if ($startTime === null && $endTime === null) {
                                continue;
                            }
                            $instanceId = $instanceLookup[(string) $locInfo['location_id']][(int) $instNum] ?? null;
                            if (! $instanceId) {
                                continue;
                            }
                            ReportEnvironmentalEntry::updateOrCreate(
                                [
                                    'report_id'               => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number'           => (int) $col,
                                    'shift'                   => $myShift,
                                ],
                                ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                            );
                        }
                    }
                    $hd['swab_times'][$secId][$instNum][$col] = array_replace_recursive(
                        $hd['swab_times'][$secId][$instNum][$col] ?? [],
                        $slotData
                    );
                }
            }
        }

        $hd['_field_owners'] = $owners;

        return [$savedSectionIds, $hd, $owners];
    }

    /**
     * Simpan exposure times (jam yang sama untuk semua lokasi seksi) dan fan-out ke entries.
     *
     * @return array [$savedSectionIds, $hd, $owners]
     */
    private function saveExposureTimes(
        array $exposureTimes, Report $report, array $sectionLocations, array $instanceLookup,
        array $savedSectionIds, array $hd, array $owners, int $myShift
    ): array {
        if (empty($exposureTimes)) {
            return [$savedSectionIds, $hd, $owners];
        }

        foreach ($exposureTimes as $secId => $instanceData) {
            if (! is_array($instanceData)) {
                continue;
            }
            foreach ($instanceData as $instNum => $data) {
                if (! is_array($data)) {
                    continue;
                }
                foreach ($data as $col => $times) {
                    if (! is_array($times)) {
                        continue;
                    }
                    $ownerKey = "exposure_times_{$secId}_{$instNum}_{$col}";
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue;
                    }
                    $hasVal = collect($times)->flatten()
                        ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if ($hasVal) {
                        $owners[$ownerKey] = (string) Auth::id();
                        $savedSectionIds["{$secId}|{$instNum}"] = true;
                        foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                            $startTime = ($times['start_time'] ?? '') ?: null;
                            $endTime   = ($times['end_time']   ?? '') ?: null;
                            if ($startTime === null && $endTime === null) {
                                continue;
                            }
                            $instanceId = $instanceLookup[(string) $locInfo['location_id']][(int) $instNum] ?? null;
                            if (! $instanceId) {
                                continue;
                            }
                            ReportEnvironmentalEntry::updateOrCreate(
                                [
                                    'report_id'               => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number'           => (int) $col,
                                    'shift'                   => $myShift,
                                ],
                                ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                            );
                        }
                    }
                    $hd['exposure_times'][$secId][$instNum][$col] = array_replace_recursive(
                        $hd['exposure_times'][$secId][$instNum][$col] ?? [],
                        $times
                    );
                }
            }
        }

        $hd['_field_owners'] = $owners;

        return [$savedSectionIds, $hd, $owners];
    }

    /**
     * Simpan data CFU dan waktu per_location dari input 'entries' ke tabel environmental_entries.
     * Entry CFU yang sudah dimiliki analis lain tidak bisa ditimpa.
     *
     * @return array $savedSectionIds yang sudah diperbarui
     */
    private function saveCfuEntries(
        Request $request, Report $report,
        array $locationSectionType, array $locationSectionId, array $locationSectionTimeSlot,
        array $instanceLookup, array $savedSectionIds, int $myShift
    ): array {
        // Kunci entry yang sudah punya data CFU dari analis lain.
        $lockedEntryKeys = ReportEnvironmentalEntry::where('report_id', $report->id)
            ->where('analyst_id', '!=', Auth::id())
            ->whereNotNull('analyst_id')
            ->where(fn ($q) => $q->whereNotNull('cfu_bacteria')->orWhereNotNull('cfu_fungi'))
            ->get()
            ->map(fn ($e) => "{$e->env_section_instance_id}-{$e->period_number}-{$e->shift}")
            ->toArray();

        foreach ($request->input('entries', []) as $locationId => $instances) {
            $sectionType = $locationSectionType[(string) $locationId] ?? null;
            if (! $sectionType) {
                continue;
            }
            $timeSlotType = $locationSectionTimeSlot[(string) $locationId] ?? 'none';
            $sectionId    = $locationSectionId[(string) $locationId] ?? null;

            foreach ($instances as $instanceNum => $cols) {
                $instanceNumber = max(1, (int) $instanceNum);

                foreach ($cols as $colIdx => $data) {
                    $periodNumber = (int) $colIdx;
                    $shift        = $myShift;

                    $hasCfuData = (($data['cfu_bacteria'] ?? '') !== '' && ($data['cfu_bacteria'] ?? null) !== null)
                               || (($data['cfu_fungi']    ?? '') !== '' && ($data['cfu_fungi']    ?? null) !== null);
                    $hasPerLocTime = ($timeSlotType === 'per_location') && (($data['start_time'] ?? '') !== '');

                    if (! $hasCfuData && ! $hasPerLocTime) {
                        continue;
                    }

                    $instanceId = $instanceLookup[(string) $locationId][$instanceNumber] ?? null;
                    if (! $instanceId) {
                        continue;
                    }

                    $entryKey = "{$instanceId}-{$periodNumber}-{$shift}";
                    if ($hasCfuData && in_array($entryKey, $lockedEntryKeys)) {
                        continue;
                    }

                    $updateValues = [];
                    if ($hasCfuData) {
                        $updateValues['analyst_id']   = Auth::id();
                        $updateValues['cfu_bacteria'] = self::normalizeCfu($data['cfu_bacteria'] ?? null);
                        $updateValues['cfu_fungi']    = self::normalizeCfu($data['cfu_fungi']    ?? null);
                    }
                    if ($hasPerLocTime) {
                        $updateValues['start_time'] = $data['start_time'];
                        $updateValues['end_time']   = null;
                        if (! isset($updateValues['analyst_id'])) {
                            $updateValues['analyst_id'] = Auth::id();
                        }
                    }

                    if (empty($updateValues)) {
                        continue;
                    }

                    ReportEnvironmentalEntry::updateOrCreate(
                        [
                            'report_id'               => $report->id,
                            'env_section_instance_id' => $instanceId,
                            'period_number'           => $periodNumber,
                            'shift'                   => $shift,
                        ],
                        $updateValues
                    );

                    if ($sectionId) {
                        $savedSectionIds["{$sectionId}|{$instanceNumber}"] = true;
                    }
                }
            }
        }

        return $savedSectionIds;
    }

    private function saveSectionColumnNames(Request $request, Report $report): void
    {
        $columnNames = $request->input('column_names', []);
        if (! is_array($columnNames) || empty($columnNames)) {
            return;
        }

        foreach ($columnNames as $sectionId => $instanceData) {
            if (! is_array($instanceData) || empty($instanceData)) {
                continue;
            }

            $firstKey = array_key_first($instanceData);
            $isFlatColumns = $firstKey !== null && ! is_array($instanceData[$firstKey]);
            if ($isFlatColumns) {
                $instanceData = [1 => $instanceData];
            }

            foreach ($instanceData as $instanceNum => $columns) {
                if (! is_array($columns) || empty($columns)) {
                    continue;
                }

                $instanceNumber = max(1, (int) $instanceNum);

                foreach ($columns as $period => $label) {
                    $periodNumber = (int) $period;
                    if ($periodNumber < 1) {
                        continue;
                    }

                    $value = is_string($label) ? trim($label) : null;

                    ReportSectionColumn::updateOrCreate(
                        [
                            'report_id' => $report->id,
                            'section_id' => $sectionId,
                            'instance_number' => $instanceNumber,
                            'period_number' => $periodNumber,
                        ],
                        [
                            'label' => $value !== '' ? $value : null,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Simpan data pemantauan personel ke personnel_instances + personnel_rows + personnel_sampling_entries.
     *
     * Format request:
     *   personnel[{instId}][row][{rowOrder}][name|time|class|activities[]|cfu[{pointId}][b|f|t|kesimpulan]]
     *   page_notes[{pageNum}][note|deviation]
     *
     * instId bisa berupa:
     *   - UUID => instance sudah ada di DB
     *   - "_new_p{N}_m{methodId}" => buat instance baru untuk page N + method methodId
     */
    private function savePersonnel(Request $request, Report $report): bool
    {
        $hasPersonnelData = false;

        // Simpan catatan/deviasi per halaman ke tabel personnel_instances (bukan JSON header_data).
        if ($request->has('page_notes')) {
            $report->loadMissing('reportType.personnelMethods');
            $methodIds = $report->reportType->personnelMethods->pluck('id')->all();

            foreach ($request->input('page_notes', []) as $pageNum => $noteData) {
                $page = (int) $pageNum;
                if ($page < 1) {
                    continue;
                }

                $note = trim($noteData['note'] ?? '');
                $deviation = trim($noteData['deviation'] ?? '');

                foreach ($methodIds as $methodId) {
                    $instance = PersonnelInstance::firstOrCreate([
                        'report_id'                   => $report->id,
                        'personnel_section_method_id' => $methodId,
                        'page_number'                 => $page,
                    ]);

                    $instance->note = $note !== '' ? $note : null;
                    $instance->deviation = $deviation !== '' ? $deviation : null;
                    $instance->save();
                }
            }
        }

        // Simpan baris data personnel per instance.
        foreach ($request->input('personnel', []) as $instId => $instData) {
            if (str_starts_with((string) $instId, '_new_')) {
                if (! preg_match('/_new_p(\d+)_m([0-9a-f\-]+)/i', (string) $instId, $m)) {
                    continue;
                }
                $instance = PersonnelInstance::firstOrCreate([
                    'report_id'                   => $report->id,
                    'personnel_section_method_id' => $m[2],
                    'page_number'                 => (int) $m[1],
                ]);
            } else {
                $instance = PersonnelInstance::where('id', $instId)
                    ->where('report_id', $report->id)
                    ->first();
                if (! $instance) {
                    continue;
                }
            }

            foreach ($instData['row'] ?? [] as $rowOrder => $rowData) {
                if (! is_array($rowData)) {
                    continue;
                }

                $nameInputProvided       = array_key_exists('name', $rowData);
                $timeInputProvided       = array_key_exists('time', $rowData);
                $classInputProvided      = array_key_exists('class', $rowData);
                $activitiesInputProvided = array_key_exists('activities', $rowData);

                $name = $nameInputProvided ? trim((string) ($rowData['name'] ?? '')) : '';
                $time = $timeInputProvided ? ($rowData['time'] ?? '') : '';
                $cls  = $classInputProvided ? ($rowData['class'] ?? null) : null;

                if ($name === '' && $time === '' && empty($rowData['cfu'] ?? [])) {
                    continue;
                }

                $hasPersonnelData = true;

                $row = PersonnelRow::firstOrNew([
                    'personnel_instance_id' => $instance->id,
                    'row_order'             => (int) $rowOrder,
                ]);

                if ($nameInputProvided) {
                    $row->personnel_name = $name ?: null;
                }
                if ($timeInputProvided) {
                    $row->monitoring_time = $time ?: null;
                }
                if ($classInputProvided) {
                    $row->class = $cls ?: null;
                }
                if ($activitiesInputProvided) {
                    $row->activities = array_values($rowData['activities'] ?? []);
                }

                $row->filled_by = Auth::id();
                $row->save();

                foreach ($rowData['cfu'] ?? [] as $pointId => $cfuData) {
                    $b = self::normalizeCfu($cfuData['b'] ?? null);
                    $f = self::normalizeCfu($cfuData['f'] ?? null);
                    $t = self::normalizeCfu($cfuData['t'] ?? null);
                    $k = in_array($cfuData['kesimpulan'] ?? '', ['MS', 'TMS'], true)
                        ? $cfuData['kesimpulan']
                        : null;

                    if ($b === null && $f === null && $t === null && $k === null) {
                        continue;
                    }

                    PersonnelSamplingEntry::updateOrCreate(
                        [
                            'personnel_row_id'  => $row->id,
                            'sampling_point_id' => $pointId,
                        ],
                        [
                            'cfu_bacteria' => $b,
                            'cfu_fungi'    => $f,
                            'cfu_total'    => $t,
                            'kesimpulan'   => $k,
                        ]
                    );
                }
            }
        }

        return $hasPersonnelData;
    }

    /**
     * Simpan identitas instrumen (air sampler) langsung ke tabel instrument_identities.
     * Digunakan oleh supervisor/manajer yang menerima data via header_data[air_sampler].
     */
    public function saveInstrumentFromArray(array $asData, Report $report): void
    {
        $report->instrumentIdentities()->updateOrCreate(
            ['tool_name' => $asData['tool_name'] ?? 'Air Sampler'],
            [
                'no_id'            => $asData['no_id']            ?? null ?: null,
                'calibration_date' => $asData['calibration_date'] ?? null ?: null,
                'due_date'         => $asData['due_date']         ?? null ?: null,
            ]
        );
    }

    /**
     * Simpan waktu paparan (exposure/settle/swab) dari form review supervisor/manajer
     * ke tabel report_environmental_entries sebagai start_time & end_time.
     *
     * Form review mengirim tanpa level instNum:
     *   exposure_times[secId][col][start_time/end_time]
     *   settle_times[secId][col][a/b][start_time/end_time]
     *   swab_times[secId][col][s1/s1_2/s1_3][mulai/selesai]
     *
     * Method ini melakukan fan-out ke semua instance yang sesuai per lokasi.
     */
    public function saveReviewTimesToEntries(
        array $settleTimes,
        array $swabTimes,
        array $exposureTimes,
        Report $report,
        int $shift = 1
    ): void {
        [$sectionLocations] = $this->buildSectionLocationMaps($report);
        $instanceLookup     = $this->buildInstanceLookup($report);

        // Exposure times: fan-out ke semua lokasi & instance untuk section + col
        foreach ($exposureTimes as $secId => $colData) {
            if (! is_array($colData)) {
                continue;
            }
            foreach ($colData as $col => $times) {
                if (! is_array($times)) {
                    continue;
                }
                $startTime = ($times['start_time'] ?? '') ?: null;
                $endTime   = ($times['end_time']   ?? '') ?: null;
                if ($startTime === null && $endTime === null) {
                    continue;
                }
                foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                    foreach ($instanceLookup[(string) $locInfo['pivot_id']] ?? [] as $instanceId) {
                        ReportEnvironmentalEntry::where([
                            'report_id'               => $report->id,
                            'env_section_instance_id' => $instanceId,
                            'period_number'           => (int) $col,
                            'shift'                   => $shift,
                        ])->update(['start_time' => $startTime, 'end_time' => $endTime]);
                    }
                }
            }
        }

        // Settle times: per kelas ruangan A/B
        foreach ($settleTimes as $secId => $colData) {
            if (! is_array($colData)) {
                continue;
            }
            foreach ($colData as $col => $abData) {
                if (! is_array($abData)) {
                    continue;
                }
                foreach ($abData as $ab => $times) {
                    if (! is_array($times)) {
                        continue;
                    }
                    $startTime = ($times['start_time'] ?? '') ?: null;
                    $endTime   = ($times['end_time']   ?? '') ?: null;
                    if ($startTime === null && $endTime === null) {
                        continue;
                    }
                    foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                        if ($locInfo['class'] !== strtolower((string) $ab)) {
                            continue;
                        }
                        foreach ($instanceLookup[(string) $locInfo['pivot_id']] ?? [] as $instanceId) {
                            ReportEnvironmentalEntry::where([
                                'report_id'               => $report->id,
                                'env_section_instance_id' => $instanceId,
                                'period_number'           => (int) $col,
                                'shift'                   => $shift,
                            ])->update(['start_time' => $startTime, 'end_time' => $endTime]);
                        }
                    }
                }
            }
        }

        // Swab times: per pola nomor lokasi S1 / S1-2 / S1-3
        foreach ($swabTimes as $secId => $colData) {
            if (! is_array($colData)) {
                continue;
            }
            foreach ($colData as $col => $swabData) {
                if (! is_array($swabData)) {
                    continue;
                }
                foreach ($swabData as $swabKey => $times) {
                    if (! is_array($times)) {
                        continue;
                    }
                    $startTime = ($times['mulai']   ?? '') ?: null;
                    $endTime   = ($times['selesai'] ?? '') ?: null;
                    if ($startTime === null && $endTime === null) {
                        continue;
                    }
                    foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                        $num = $locInfo['location_number'];
                        $matchS1_3 = stripos($num, 'S1-3') !== false;
                        $matchS1_2 = stripos($num, 'S1-2') !== false;
                        if ($swabKey === 's1_3' && ! $matchS1_3) {
                            continue;
                        }
                        if ($swabKey === 's1_2' && ! $matchS1_2) {
                            continue;
                        }
                        if ($swabKey === 's1' && ($matchS1_2 || $matchS1_3)) {
                            continue;
                        }
                        foreach ($instanceLookup[(string) $locInfo['pivot_id']] ?? [] as $instanceId) {
                            ReportEnvironmentalEntry::where([
                                'report_id'               => $report->id,
                                'env_section_instance_id' => $instanceId,
                                'period_number'           => (int) $col,
                                'shift'                   => $shift,
                            ])->update(['start_time' => $startTime, 'end_time' => $endTime]);
                        }
                    }
                }
            }
        }
    }
}
