<?php

namespace Domain\Report\Services;

use Domain\Report\Interfaces\ReportEntryRepositoryInterface;
use Domain\Report\Models\EnvSectionInstance;
use Domain\Report\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EnvironmentalEntryService
 *
 * Menangani semua persistence data environmental entry:
 * - build mapping lokasi/instance
 * - fan-out waktu paparan (settle/swab/exposure)
 * - upsert CFU + per_location time
 * - sinkronisasi review times supervisor/manajer ke entries
 */
class EnvironmentalEntryService
{
    public function __construct(private ReportEntryRepositoryInterface $repository) {}

    /**
     * Validate CFU values from nested entries payload.
     *
     * @param  array<string, mixed>  $entries
     * @return array<int, string>
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
     * Bangun peta lokasi per section dan lookup table per pivot_id.
     *
     * @return array{0: array<string, array<int, array<string, mixed>>>, 1: array<string, string>, 2: array<string, string>, 3: array<string, string>}
     */
    public function buildSectionLocationMaps(Report $report): array
    {
        $sectionLocations = [];
        $locationSectionType = [];
        $locationSectionId = [];
        $locationSectionTimeSlot = [];

        foreach ($report->reportType->sections()->with('locations.room')->get() as $_sec) {
            $sectionLocations[$_sec->id] = [];
            foreach ($_sec->locations as $_loc) {
                $locationId = (string) $_loc->id;
                $cls = strtolower($_loc->room->class ?? '');
                $num = $_loc->location_number ?? '';
                $sectionLocations[$_sec->id][] = [
                    'location_id' => $locationId,
                    'class' => $cls,
                    'location_number' => $num,
                ];
                $locationSectionType[$locationId] = $_sec->measurement_key;
                $locationSectionId[$locationId] = $_sec->id;
                $locationSectionTimeSlot[$locationId] = $_sec->time_slot_type;
            }
        }

        return [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot];
    }

    /**
     * Bangun lookup: $instanceLookup[location_id][instance_number] = env_section_instance_id.
     *
     * @return array<string, array<int, string>>
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
     * Simpan settle times (section dual_ab: jam per kelas ruangan A/B) dan fan-out ke entries.
     *
     * @param  array<string, mixed>  $settleTimes
     * @param  array<string, array<int, array<string, mixed>>>  $sectionLocations
     * @param  array<string, array<int, string>>  $instanceLookup
     * @param  array<string, bool>  $savedSectionIds
     * @return array<string, bool>
     */
    public function saveSettleTimes(
        array $settleTimes,
        Report $report,
        array $sectionLocations,
        array $instanceLookup,
        array $savedSectionIds
    ): array {
        if (empty($settleTimes)) {
            return $savedSectionIds;
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
                    $hasVal = collect($abData)->flatten()
                        ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if ($hasVal) {
                        $savedSectionIds["{$secId}|{$instNum}"] = true;
                        foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                            $ab = $locInfo['class'];
                            $st = $abData[$ab] ?? [];
                            $startTime = ($st['start_time'] ?? '') ?: null;
                            $endTime = ($st['end_time'] ?? '') ?: null;
                            if ($startTime === null && $endTime === null) {
                                continue;
                            }
                            $instanceId = $instanceLookup[(string) $locInfo['location_id']][(int) $instNum] ?? null;
                            if (! $instanceId) {
                                continue;
                            }
                            $this->repository->upsertEnvironmentalEntry(
                                [
                                    'report_id' => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number' => (int) $col,
                                    'shift' => 1,
                                ],
                                ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                            );
                        }
                    }
                }
            }
        }

        return $savedSectionIds;
    }

    /**
     * Simpan swab times dan fan-out ke entries.
     *
     * @param  array<string, mixed>  $swabTimes
     * @param  array<string, array<int, array<string, mixed>>>  $sectionLocations
     * @param  array<string, array<int, string>>  $instanceLookup
     * @param  array<string, bool>  $savedSectionIds
     * @return array<string, bool>
     */
    public function saveSwabTimes(
        array $swabTimes,
        Report $report,
        array $sectionLocations,
        array $instanceLookup,
        array $savedSectionIds
    ): array {
        if (empty($swabTimes)) {
            return $savedSectionIds;
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
                    $hasVal = collect($slotData)->flatten()
                        ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if ($hasVal) {
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
                            $st = $slotData[$swabKey] ?? [];
                            $startTime = ($st['mulai'] ?? '') ?: null;
                            $endTime = ($st['selesai'] ?? '') ?: null;
                            if ($startTime === null && $endTime === null) {
                                continue;
                            }
                            $instanceId = $instanceLookup[(string) $locInfo['location_id']][(int) $instNum] ?? null;
                            if (! $instanceId) {
                                continue;
                            }
                            $this->repository->upsertEnvironmentalEntry(
                                [
                                    'report_id' => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number' => (int) $col,
                                    'shift' => 1,
                                ],
                                ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                            );
                        }
                    }
                }
            }
        }

        return $savedSectionIds;
    }

    /**
     * Simpan exposure times dan fan-out ke entries.
     *
     * @param  array<string, mixed>  $exposureTimes
     * @param  array<string, array<int, array<string, mixed>>>  $sectionLocations
     * @param  array<string, array<int, string>>  $instanceLookup
     * @param  array<string, bool>  $savedSectionIds
     * @return array<string, bool>
     */
    public function saveExposureTimes(
        array $exposureTimes,
        Report $report,
        array $sectionLocations,
        array $instanceLookup,
        array $savedSectionIds
    ): array {
        if (empty($exposureTimes)) {
            return $savedSectionIds;
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
                    $hasVal = collect($times)->flatten()
                        ->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if ($hasVal) {
                        $savedSectionIds["{$secId}|{$instNum}"] = true;
                        foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                            $startTime = ($times['start_time'] ?? '') ?: null;
                            $endTime = ($times['end_time'] ?? '') ?: null;
                            if ($startTime === null && $endTime === null) {
                                continue;
                            }
                            $instanceId = $instanceLookup[(string) $locInfo['location_id']][(int) $instNum] ?? null;
                            if (! $instanceId) {
                                continue;
                            }
                            $this->repository->upsertEnvironmentalEntry(
                                [
                                    'report_id' => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number' => (int) $col,
                                    'shift' => 1,
                                ],
                                ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                            );
                        }
                    }
                }
            }
        }

        return $savedSectionIds;
    }

    /**
     * Simpan data CFU dan waktu per_location dari input 'entries'.
     *
     * @param  array<string, string>  $locationSectionType
     * @param  array<string, string>  $locationSectionId
     * @param  array<string, string>  $locationSectionTimeSlot
     * @param  array<string, array<int, string>>  $instanceLookup
     * @param  array<string, bool>  $savedSectionIds
     * @return array<string, bool>
     */
    public function saveCfuEntries(
        Request $request,
        Report $report,
        array $locationSectionType,
        array $locationSectionId,
        array $locationSectionTimeSlot,
        array $instanceLookup,
        array $savedSectionIds
    ): array {
        $lockedEntryKeys = $this->repository->getLockedEnvironmentalEntryKeys(
            (string) $report->id,
            (string) Auth::id()
        );

        foreach ($request->input('entries', []) as $locationId => $instances) {
            $sectionType = $locationSectionType[(string) $locationId] ?? null;
            if (! $sectionType) {
                continue;
            }
            $timeSlotType = $locationSectionTimeSlot[(string) $locationId] ?? 'none';
            $sectionId = $locationSectionId[(string) $locationId] ?? null;

            foreach ($instances as $instanceNum => $cols) {
                $instanceNumber = max(1, (int) $instanceNum);

                foreach ($cols as $colIdx => $data) {
                    $periodNumber = (int) $colIdx;
                    $shift = 1;

                    $hasCfuData = (($data['cfu_bacteria'] ?? '') !== '' && ($data['cfu_bacteria'] ?? null) !== null)
                        || (($data['cfu_fungi'] ?? '') !== '' && ($data['cfu_fungi'] ?? null) !== null);
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
                        $updateValues['analyst_id'] = Auth::id();
                        $updateValues['cfu_bacteria'] = self::normalizeCfu($data['cfu_bacteria'] ?? null);
                        $updateValues['cfu_fungi'] = self::normalizeCfu($data['cfu_fungi'] ?? null);
                    }
                    if ($hasPerLocTime) {
                        $updateValues['start_time'] = $data['start_time'];
                        $updateValues['end_time'] = null;
                        if (! isset($updateValues['analyst_id'])) {
                            $updateValues['analyst_id'] = Auth::id();
                        }
                    }

                    if (empty($updateValues)) {
                        continue;
                    }

                    $this->repository->upsertEnvironmentalEntry(
                        [
                            'report_id' => $report->id,
                            'env_section_instance_id' => $instanceId,
                            'period_number' => $periodNumber,
                            'shift' => $shift,
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

    /**
     * Simpan waktu paparan dari form review supervisor/manajer.
     *
     * @param  array<string, mixed>  $settleTimes
     * @param  array<string, mixed>  $swabTimes
     * @param  array<string, mixed>  $exposureTimes
     */
    public function saveReviewTimesToEntries(
        array $settleTimes,
        array $swabTimes,
        array $exposureTimes,
        Report $report,
        int $shift = 1
    ): void {
        [$sectionLocations] = $this->buildSectionLocationMaps($report);
        $instanceLookup = $this->buildInstanceLookup($report);

        foreach ($exposureTimes as $secId => $colData) {
            if (! is_array($colData)) {
                continue;
            }
            foreach ($colData as $col => $times) {
                if (! is_array($times)) {
                    continue;
                }
                $startTime = ($times['start_time'] ?? '') ?: null;
                $endTime = ($times['end_time'] ?? '') ?: null;
                if ($startTime === null && $endTime === null) {
                    continue;
                }
                foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                    foreach ($instanceLookup[(string) $locInfo['location_id']] ?? [] as $instanceId) {
                        $this->repository->updateEnvironmentalEntryTimes(
                            [
                                'report_id' => $report->id,
                                'env_section_instance_id' => $instanceId,
                                'period_number' => (int) $col,
                                'shift' => $shift,
                            ],
                            $startTime,
                            $endTime
                        );
                    }
                }
            }
        }

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
                    $endTime = ($times['end_time'] ?? '') ?: null;
                    if ($startTime === null && $endTime === null) {
                        continue;
                    }
                    foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                        if ($locInfo['class'] !== strtolower((string) $ab)) {
                            continue;
                        }
                        foreach ($instanceLookup[(string) $locInfo['location_id']] ?? [] as $instanceId) {
                            $this->repository->updateEnvironmentalEntryTimes(
                                [
                                    'report_id' => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number' => (int) $col,
                                    'shift' => $shift,
                                ],
                                $startTime,
                                $endTime
                            );
                        }
                    }
                }
            }
        }

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
                    $startTime = ($times['mulai'] ?? '') ?: null;
                    $endTime = ($times['selesai'] ?? '') ?: null;
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
                        foreach ($instanceLookup[(string) $locInfo['location_id']] ?? [] as $instanceId) {
                            $this->repository->updateEnvironmentalEntryTimes(
                                [
                                    'report_id' => $report->id,
                                    'env_section_instance_id' => $instanceId,
                                    'period_number' => (int) $col,
                                    'shift' => $shift,
                                ],
                                $startTime,
                                $endTime
                            );
                        }
                    }
                }
            }
        }
    }

    private static function normalizeCfu(mixed $raw): ?string
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
}
