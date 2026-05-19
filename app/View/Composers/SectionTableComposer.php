<?php

namespace App\View\Composers;

use App\Helpers\CfuHelper;
use App\Services\ReportSectionService;
use Domain\Report\Interfaces\FieldLockRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SectionTableComposer
{
    public function __construct(
        private ReportSectionService $sectionService,
        private FieldLockRepositoryInterface $fieldLockRepository,
    ) {}

    public function compose(View $view): void
    {
        $data       = $view->getData();
        $section    = $data['section'];
        $instance   = $data['instance'] ?? 1;
        $report     = $data['report'];
        $isEditable = $data['isEditable'] ?? false;
        $entryMap   = $data['entryMap'] ?? [];

        $maxCols   = $section->max_column;
        $romanNums = ['I', 'II', 'III', 'IV', 'V', 'VI'];

        $secTimesFromEntries = [];
        foreach ($section->locations as $loc) {
            $locId = (string) $loc->id;
            $locClass = strtolower((string) ($loc->room->class ?? ''));
            $locNum = (string) ($loc->location_number ?? '');
            $isS1_3 = stripos($locNum, 'S1-3') !== false;
            $isS1_2 = stripos($locNum, 'S1-2') !== false;
            $swabKey = $isS1_3 ? 's1_3' : ($isS1_2 ? 's1_2' : 's1');

            for ($col = 0; $col <= $maxCols; $col++) {
                $entry = $entryMap[$locId][$instance][$col][1] ?? $entryMap[$locId][$instance][$col][2] ?? null;
                if (! $entry || (! $entry->start_time && ! $entry->end_time)) {
                    continue;
                }

                if ($locClass !== '' && ! isset($secTimesFromEntries[$col][$locClass])) {
                    $secTimesFromEntries[$col][$locClass] = [
                        'start_time' => $entry->start_time,
                        'end_time' => $entry->end_time,
                    ];
                }

                if (! isset($secTimesFromEntries[$col]['swab'][$swabKey])) {
                    $secTimesFromEntries[$col]['swab'][$swabKey] = [
                        'mulai' => $entry->start_time,
                        'selesai' => $entry->end_time,
                    ];
                }

                if (! isset($secTimesFromEntries[$col]['start_time'])) {
                    $secTimesFromEntries[$col]['start_time'] = $entry->start_time;
                    $secTimesFromEntries[$col]['end_time'] = $entry->end_time;
                }
            }
        }

        $hasMachineSetup = (bool) $section->has_machine_setup;
        $hasTime         = $section->time_slot_type === 'single';
        $isPerLocation   = $section->time_slot_type === 'per_location';
        $isDualAB        = $section->time_slot_type === 'dual_ab';
        $isSwabTime      = $section->time_slot_type === 'swab';
        $isSettlePlate   = $section->measurement_key === 'settle_plate';
        $colLabelRaw     = is_string($section->column_label) ? trim($section->column_label) : null;
        $colLabel        = $colLabelRaw !== '' ? $colLabelRaw : null;
        $subColsPerExp   = $isPerLocation ? 4 : 3;

        $isMonitoring = $report->status === 'monitoring';
        $isReading    = $report->status === 'reading';
        $currentUserId = (string) (Auth::id() ?? '');

        $ms0Locked = false;
        $msHasTime = ! empty($secTimesFromEntries[0]['start_time'] ?? null);

        $secAssignments = [];
        for ($c = 1; $c <= $maxCols; $c++) {
            $secAssignments[$c] = 1;
        }

        $columnRows = $report->relationLoaded('sectionColumnNames')
            ? $report->sectionColumnNames
            : $report->sectionColumnNames()->get();
        $columnNames = $columnRows
            ->where('section_id', $section->id)
            ->where('instance_number', (int) $instance)
            ->keyBy(fn ($row) => (int) $row->period_number)
            ->map(fn ($row) => $row->label)
            ->all();

        $columnRowIds = $columnRows
            ->where('section_id', $section->id)
            ->where('instance_number', (int) $instance)
            ->keyBy(fn ($row) => (int) $row->period_number)
            ->map(fn ($row) => (string) $row->id)
            ->all();

        $columnLabelLockedByOther = [];
        foreach ($columnRowIds as $periodNum => $rowId) {
            if ($rowId === '') {
                $columnLabelLockedByOther[$periodNum] = false;
                continue;
            }

            $currentLabel = trim((string) ($columnNames[$periodNum] ?? ''));
            if ($currentLabel === '') {
                $columnLabelLockedByOther[$periodNum] = false;
                continue;
            }

            $ownerMap = $this->fieldLockRepository->getOwnerMap(
                'report_section_columns',
                $rowId,
                ['label']
            );
            $owner = $ownerMap['label'] ?? null;
            $columnLabelLockedByOther[$periodNum] = $owner !== null && $owner !== $currentUserId;
        }

        $timeEntryRowIds = [];
        foreach ($section->locations as $loc) {
            $locId = (string) $loc->id;
            foreach (($entryMap[$locId][$instance] ?? []) as $shifts) {
                foreach ($shifts as $entry) {
                    if ($entry?->id) {
                        $timeEntryRowIds[] = (string) $entry->id;
                    }
                }
            }
        }
        $timeEntryRowIds = array_values(array_unique($timeEntryRowIds));

        $timeLockOwnerByRow = $this->fieldLockRepository->getOwnerMapByRows(
            'report_environmental_entries',
            $timeEntryRowIds,
            ['start_time', 'end_time']
        );

        $timeLockedByOther = [];
        foreach ($section->locations as $loc) {
            $locId    = (string) $loc->id;
            $locClass = strtolower((string) ($loc->room->class ?? ''));
            $locNum   = (string) ($loc->location_number ?? '');
            $swabKey = stripos($locNum, 'S1-3') !== false
                ? 's1_3'
                : (stripos($locNum, 'S1-2') !== false ? 's1_2' : 's1');

            foreach (($entryMap[$locId][$instance] ?? []) as $col => $shifts) {
                foreach ($shifts as $entry) {
                    if (! $entry?->id) {
                        continue;
                    }

                    $ownerMap = $timeLockOwnerByRow[(string) $entry->id] ?? [];
                    $hasStartValue = $this->normalizeTimeValue($entry->start_time) !== null;
                    $hasEndValue = $this->normalizeTimeValue($entry->end_time) !== null;
                    $startLocked = $hasStartValue
                        && isset($ownerMap['start_time'])
                        && $ownerMap['start_time'] !== $currentUserId;
                    $endLocked = $hasEndValue
                        && isset($ownerMap['end_time'])
                        && $ownerMap['end_time'] !== $currentUserId;

                    if (! $startLocked && ! $endLocked) {
                        continue;
                    }

                    if ($isDualAB && $locClass !== '') {
                        $timeLockedByOther[$col][$locClass]['start_time'] =
                            ($timeLockedByOther[$col][$locClass]['start_time'] ?? false) || $startLocked;
                        $timeLockedByOther[$col][$locClass]['end_time'] =
                            ($timeLockedByOther[$col][$locClass]['end_time'] ?? false) || $endLocked;
                        continue;
                    }

                    if ($isSwabTime) {
                        $timeLockedByOther[$col]['swab'][$swabKey]['mulai'] =
                            ($timeLockedByOther[$col]['swab'][$swabKey]['mulai'] ?? false) || $startLocked;
                        $timeLockedByOther[$col]['swab'][$swabKey]['selesai'] =
                            ($timeLockedByOther[$col]['swab'][$swabKey]['selesai'] ?? false) || $endLocked;
                        continue;
                    }

                    $timeLockedByOther[$col]['start_time'] =
                        ($timeLockedByOther[$col]['start_time'] ?? false) || $startLocked;
                    $timeLockedByOther[$col]['end_time'] =
                        ($timeLockedByOther[$col]['end_time'] ?? false) || $endLocked;
                }
            }
        }

        $ms0TimeLockedByOther = [
            'start_time' => (bool) ($timeLockedByOther[0]['start_time'] ?? false),
            'end_time' => (bool) ($timeLockedByOther[0]['end_time'] ?? false),
        ];

        $totalCols = 5 + ($hasMachineSetup ? 3 : 0) + ($maxCols * $subColsPerExp) + 5;

        $freqOrder   = ['operational', 'daily', 'weekly', 'monthly', 'semi_annual'];
        $locsByFreq  = $section->locations->groupBy(fn ($loc) => $loc->frequency ?? '__');
        $freqKeys    = collect($freqOrder)
            ->filter(fn ($freq) => $locsByFreq->has($freq))
            ->merge($locsByFreq->keys()->filter(fn ($key) => ! in_array($key, $freqOrder) && $key !== '__'))
            ->values();
        if ($locsByFreq->has('__')) {
            $freqKeys->push('__');
        }
        $showFreqHdr = $freqKeys->count() > 1
            || ($freqKeys->count() === 1 && $freqKeys->first() !== '__');

        $cfuNum = fn (?string $value): ?int => CfuHelper::toInt($value);
        $cfuTot = fn (?string $bacteria, ?string $fungi): ?string => CfuHelper::total($bacteria, $fungi);

        $totalInstances    = (int) ($data['totalInstances'] ?? 1);
        $sectionConclusion = $this->sectionService->computeSectionConclusion($section, $entryMap, $totalInstances);
        $sectionNoteRows = $report->relationLoaded('sectionNotes')
            ? $report->sectionNotes
            : $report->sectionNotes()->get();
        $sectionNoteRow = $sectionNoteRows->first(
            fn ($row) => (string) $row->section_id === (string) $section->id
                && (int) ($row->instance_number ?? 1) === (int) $instance
        );
        $sectionNote = [
            'notes' => $sectionNoteRow?->notes,
            'conclusion' => $sectionNoteRow?->conclusion,
        ];

        $view->with(compact(
            'hasMachineSetup', 'hasTime', 'isPerLocation', 'isDualAB', 'isSwabTime',
            'isSettlePlate',
            'colLabel', 'subColsPerExp',
            'isMonitoring', 'isReading',
            'ms0Locked', 'msHasTime',
            'secTimesFromEntries',
            'secAssignments', 'columnNames', 'maxCols', 'romanNums',
            'columnLabelLockedByOther', 'timeLockedByOther', 'ms0TimeLockedByOther',
            'totalCols',
            'freqOrder', 'locsByFreq', 'freqKeys', 'showFreqHdr',
            'cfuNum', 'cfuTot',
            'sectionConclusion', 'sectionNote',
        ));
    }

    private function normalizeTimeValue(mixed $raw): ?string
    {
        $value = trim((string) ($raw ?? ''));

        return $value !== '' ? $value : null;
    }
}
