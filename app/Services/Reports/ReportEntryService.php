<?php

namespace App\Services\Reports;

use App\Domains\ReportEntry\InstrumentIdentityEntry\Services\InstrumentIdentityEntryService;
use App\Domains\ReportEntry\MediumEntry\Services\MediumEntryService;
use App\Domains\ReportEntry\Shared\Services\EnvironmentalEntryService;
use App\Domains\ReportEntry\Shared\Services\PersonnelEntryService;
use App\Models\Analyst;
use App\Models\Report;
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
    public function __construct(
        private InstrumentIdentityEntryService $instrumentIdentityEntryService,
        private MediumEntryService $mediumEntryService,
        private EnvironmentalEntryService $environmentalEntryService,
        private PersonnelEntryService $personnelEntryService,
    ) {}

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
     * Migrasi format ownership lama (per-section) ke format baru (per-field).
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
        $this->instrumentIdentityEntryService->saveFromRequest($request, $report);

        //  2. IDENTITAS MEDIUM AGAR 
        $this->saveMediums($request, $report);

        //  3. DATA INKUBASI 
        // Menyimpan ke DB dan mengembalikan header_data yang sudah diperbarui.
        $hd = $this->saveIncubators($request, $report);

        //  4 & 5. HEADER_DATA + ANALIS + SHIFT ASSIGNMENT 
        [$hd, $owners] = $this->saveHeaderData($request, $report, $hd);

        $this->saveAnalysts($request, $report);
        $this->personnelEntryService->saveSectionColumnNames($request, $report);

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
            $this->environmentalEntryService->buildSectionLocationMaps($report);
        $instanceLookup = $this->environmentalEntryService->buildInstanceLookup($report);

        //  7. WAKTU PAPARAN (FAN-OUT KE ENTRIES) 
        $settleTimes   = $request->input('settle_times',   []);
        $swabTimes     = $request->input('swab_times',     []);
        $exposureTimes = $request->input('exposure_times', []);

        [$savedSectionIds, $hd, $owners] = $this->environmentalEntryService->saveSettleTimes(
            $settleTimes, $report, $sectionLocations, $instanceLookup,
            $savedSectionIds, $hd, $owners, $myShift
        );
        [$savedSectionIds, $hd, $owners] = $this->environmentalEntryService->saveSwabTimes(
            $swabTimes, $report, $sectionLocations, $instanceLookup,
            $savedSectionIds, $hd, $owners, $myShift
        );
        [$savedSectionIds, $hd, $owners] = $this->environmentalEntryService->saveExposureTimes(
            $exposureTimes, $report, $sectionLocations, $instanceLookup,
            $savedSectionIds, $hd, $owners, $myShift
        );

        // Simpan header_data jika ada blok 4, 5, atau 6 yang menghasilkan perubahan.
        if ($request->has('header_data') || ! empty($shiftAssignment)
            || ! empty($settleTimes) || ! empty($swabTimes) || ! empty($exposureTimes)) {
            $hd['_field_owners'] = $owners;
            $report->update(['header_data' => $hd]);
        }

        // 7. CFU UPSERT + PER_LOCATION TIME 
        $savedSectionIds = $this->environmentalEntryService->saveCfuEntries(
            $request, $report, $locationSectionType, $locationSectionId,
            $locationSectionTimeSlot, $instanceLookup, $savedSectionIds, $myShift
        );

        // 8. PEMANTAUAN PERSONEL 
        $hasPersonnelData = false;
        if ($request->has('personnel') || $request->has('page_notes')) {
            $hasPersonnelData = $this->personnelEntryService->savePersonnel($request, $report);
        }

        return [$savedSectionIds, $hasPersonnelData];
    }

    // Private helper methods

    private function saveMediums(Request $request, Report $report): void
    {
        $this->mediumEntryService->saveFromRequest($request, $report);
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

        $report->loadMissing('reportType.incubatorTypes');

        foreach ($request->input('incubator', []) as $tempKey => $data) {
            $rti = $report->reportType->incubatorTypes->firstWhere('id', $tempKey);
            if (! $rti || ! is_array($data)) {
                continue;
            }

            $infoFields = array_filter([
                'no_id'                => $data['no_id']                ?? null ?: null,
                'calibration_date'     => $data['calibration_date']     ?? null ?: null,
                'due_date_calibration' => $data['due_date_calibration'] ?? ($data['due_date'] ?? null) ?: null,
            ]);

            $ownerKeyInfo = "incubator_{$tempKey}_info";

            $infoLocked = isset($inkOwners[$ownerKeyInfo]) && (string) $inkOwners[$ownerKeyInfo] !== (string) Auth::id();
            if (! $infoLocked) {
                if (! empty($infoFields)) {
                    $inkOwners[$ownerKeyInfo] = (string) Auth::id();
                }
            } else {
                $infoFields = [];
            }

            $incubator = null;
            if (! empty($infoFields)) {
                $incubator = $report->incubators()->firstOrCreate(
                    ['report_type_incubator_id' => $tempKey],
                    ['report_type_incubator_id' => $tempKey]
                );
                $incubator->fill($infoFields)->save();
            }

            // Format baru: incubator[<rti_id>][monitoring|swab][field]
            $entryPayloads = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $entryPayloads[$key] = $value;
                }
            }

            // Backward compatibility format lama (flat fields) → mapping ke medium_type 'monitoring'.
            if (empty($entryPayloads) && (
                isset($data['incubated_by']) || isset($data['date_in']) || isset($data['time_in']) ||
                isset($data['removed_by']) || isset($data['date_out']) || isset($data['time_out'])
            )) {
                $entryPayloads['monitoring'] = [
                    'incubated_by' => $data['incubated_by'] ?? null,
                    'date_in'      => $data['date_in'] ?? null,
                    'time_in'      => $data['time_in'] ?? null,
                    'removed_by'   => $data['removed_by'] ?? null,
                    'date_out'     => $data['date_out'] ?? null,
                    'time_out'     => $data['time_out'] ?? null,
                ];
            }

            foreach ($entryPayloads as $mediumType => $entryData) {
                if (! is_array($entryData)) {
                    continue;
                }
                if (! in_array((string) $mediumType, ['monitoring', 'swab'], true)) {
                    continue;
                }

                $inFields = array_filter([
                    'incubated_by' => $entryData['incubated_by'] ?? null ?: null,
                    'date_in'      => $entryData['date_in']      ?? null ?: null,
                    'time_in'      => $entryData['time_in']      ?? null ?: null,
                ]);
                $outFields = array_filter([
                    'removed_by' => $entryData['removed_by'] ?? null ?: null,
                    'date_out'   => $entryData['date_out']   ?? null ?: null,
                    'time_out'   => $entryData['time_out']   ?? null ?: null,
                ]);

                $ownerKeyIn  = "incubator_{$tempKey}_{$mediumType}_in";
                $ownerKeyOut = "incubator_{$tempKey}_{$mediumType}_out";

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

                $mergedEntry = array_merge($inFields, $outFields);
                if (! empty($mergedEntry)) {
                    if ($incubator === null) {
                        $incubator = $report->incubators()->firstOrCreate(
                            ['report_type_incubator_id' => $tempKey],
                            ['report_type_incubator_id' => $tempKey]
                        );
                    }
                    $incubator->entries()->updateOrCreate(
                        ['medium_type' => (string) $mediumType],
                        $mergedEntry
                    );
                }
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

    /**
     * Bangun peta lokasi per section dan lookup table per pivot_id.
     *
     * @return array [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot]
     */
    public function buildSectionLocationMaps(Report $report): array
    {
        return $this->environmentalEntryService->buildSectionLocationMaps($report);
    }

    /**
     * Bangun lookup: $instanceLookup[location_id][instance_number] = env_section_instance_id.
     */
    public function buildInstanceLookup(Report $report): array
    {
        return $this->environmentalEntryService->buildInstanceLookup($report);
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
        $this->environmentalEntryService->saveReviewTimesToEntries(
            $settleTimes,
            $swabTimes,
            $exposureTimes,
            $report,
            $shift
        );
    }
}
