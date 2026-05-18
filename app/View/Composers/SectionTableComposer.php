<?php

namespace App\View\Composers;

use App\Helpers\CfuHelper;
use App\Services\ReportSectionService;
use Domain\Report\Interfaces\FieldLockRepositoryInterface;
use Domain\Report\Interfaces\ReportEntryRepositoryInterface;
use Illuminate\View\View;

class SectionTableComposer
{
    public function __construct(
        private ReportSectionService $sectionService,
        private FieldLockRepositoryInterface $fieldLockRepository,
        private ReportEntryRepositoryInterface $repository,
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

        // Build time lookup from report_environmental_entries (normalized source).
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

        // ── Time-slot type flags ──────────────────────────────────────────────
        $hasMachineSetup = (bool) $section->has_machine_setup;
        $hasTime         = $section->time_slot_type === 'single';
        $isPerLocation   = $section->time_slot_type === 'per_location';
        $isDualAB        = $section->time_slot_type === 'dual_ab';
        $isSwabTime      = $section->time_slot_type === 'swab';
        $isSettlePlate   = $section->measurement_key === 'settle_plate';
        $colLabelRaw     = is_string($section->column_label) ? trim($section->column_label) : null;
        $colLabel        = $colLabelRaw !== '' ? $colLabelRaw : null;
        $subColsPerExp   = $isPerLocation ? 4 : 3;

        // ── Phase flags ───────────────────────────────────────────────────────
        $isMonitoring = $report->status === 'monitoring';
        $isReading    = $report->status === 'reading';

        // Current analyst ID — used for lock checks throughout this method
        $currentUserId = (string) (auth()->id() ?? '');

        // ── Machine Set-up ownership ──────────────────────────────────────────
        $ms0Locked = false;
        $msHasTime = ! empty($secTimesFromEntries[0]['start_time'] ?? null);

        // ── Column shift assignments ──────────────────────────────────────────
        $secAssignments = [];
        for ($c = 1; $c <= $maxCols; $c++) {
            $secAssignments[$c] = 1;
        }

        // ── Per-column display names (settle: SP, others: Shift) ─────────────
        $columnRows = $report->relationLoaded('sectionColumnNames')
            ? $report->sectionColumnNames
            : $report->sectionColumnNames()->get();
        $columnNames = $columnRows
            ->where('section_id', $section->id)
            ->where('instance_number', (int) $instance)
            ->keyBy(fn ($r) => (int) $r->period_number)
            ->map(fn ($r) => $r->label)
            ->all();

        // ── Column name lock owners (keyed by period_number) ──────────────────
        // Used in thead to render SP/Shift inputs as readonly if locked by another analyst.
        // $currentUserId already defined above
        $columnNameLocks = $columnRows
            ->where('section_id', $section->id)
            ->where('instance_number', (int) $instance)
            ->keyBy(fn ($r) => (int) $r->period_number)
            ->map(fn ($r) => (string) ($r->lockedBy ?? ''))
            ->all();
        // We store lock ownership directly on the row via field_locks table.
        // Pass the row IDs so thead can check per-period lock status.
        $columnRowIds = $columnRows
            ->where('section_id', $section->id)
            ->where('instance_number', (int) $instance)
            ->keyBy(fn ($r) => (int) $r->period_number)
            ->map(fn ($r) => (string) $r->id)
            ->all();

        // ── Column label lock: per period, is it locked by someone else? ─────
        // Key: period_number (int) => bool (true = locked by another analyst)
        $columnLabelLockedByOther = [];
        foreach ($columnRowIds as $periodNum => $rowId) {
            if ($rowId === '') {
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

        // ── Time-entry lock: per period (and per AB-class for dual_ab), locked by another? ──
        // For dual_ab sections: keyed by col => ['a' => bool, 'b' => bool]
        // For other sections:   keyed by col => true|false
        $lockedTimeKeys = $this->repository->getLockedTimeEntryKeys(
            (string) $report->id,
            $currentUserId
        );

        $timeLockedByOther = [];
        foreach ($section->locations as $loc) {
            $locId    = (string) $loc->id;
            $locClass = strtolower((string) ($loc->room->class ?? ''));
            if (! isset($data['entryMap'][$locId])) {
                continue;
            }
            foreach ($data['entryMap'][$locId][$instance] ?? [] as $col => $shifts) {
                foreach ($shifts as $shift => $entry) {
                    if (! $entry) {
                        continue;
                    }
                    $key = "{$entry->env_section_instance_id}-{$entry->period_number}-{$entry->shift}";
                    if (! in_array($key, $lockedTimeKeys, true)) {
                        continue;
                    }
                    if ($isDualAB && $locClass !== '') {
                        // Per-class lock for dual_ab (A and B tracked separately)
                        $timeLockedByOther[$col][$locClass] = true;
                    } else {
                        $timeLockedByOther[$col] = true;
                    }
                }
            }
        }

        // Machine Set-up (period 0) time lock
        $ms0TimeLockedByOther = false;
        foreach ($section->locations as $loc) {
            $locId  = (string) $loc->id;
            $entry0 = $data['entryMap'][$locId][$instance][0][1]
                ?? $data['entryMap'][$locId][$instance][0][2]
                ?? null;
            if (! $entry0) {
                continue;
            }
            $key0 = "{$entry0->env_section_instance_id}-0-{$entry0->shift}";
            if (in_array($key0, $lockedTimeKeys, true)) {
                $ms0TimeLockedByOther = true;
                break;
            }
        }
        // Fixed: No. | Nama Ruangan | Kelas | No. Ruangan | No. Lokasi = 5
        // Then machine-setup (3 if present) + exposures + AL×2 + AA×2 + Kesimpulan = 5
        $totalCols = 5 + ($hasMachineSetup ? 3 : 0) + ($maxCols * $subColsPerExp) + 5;

        // ── Location grouping by frequency (for thead-row computation) ────────
        $freqOrder   = ['operational', 'daily', 'weekly', 'monthly', 'semi_annual'];
        $locsByFreq  = $section->locations->groupBy(fn ($loc) => $loc->frequency ?? '__');
        $freqKeys    = collect($freqOrder)
            ->filter(fn ($f) => $locsByFreq->has($f))
            ->merge($locsByFreq->keys()->filter(fn ($k) => ! in_array($k, $freqOrder) && $k !== '__'))
            ->values();
        if ($locsByFreq->has('__')) {
            $freqKeys->push('__');
        }
        $showFreqHdr = $freqKeys->count() > 1
            || ($freqKeys->count() === 1 && $freqKeys->first() !== '__');

        // ── CFU helper closures (delegate to CfuHelper) ───────────────────────
        // Kept as closures so existing blade call-sites ($cfuNum / $cfuTot) need no change.
        $cfuNum = fn (?string $v): ?int => CfuHelper::toInt($v);
        $cfuTot = fn (?string $b, ?string $f): ?string => CfuHelper::total($b, $f);

        // ── Section-level conclusion (only meaningful for instance === 1 render) ─
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
            // Type flags
            'hasMachineSetup', 'hasTime', 'isPerLocation', 'isDualAB', 'isSwabTime',
            'isSettlePlate',
            'colLabel', 'subColsPerExp',
            // Phase
            'isMonitoring', 'isReading',
            // Machine set-up
            'ms0Locked', 'msHasTime',
            'secTimesFromEntries',
            // Columns
            'secAssignments', 'columnNames', 'maxCols', 'romanNums',
            // Column label locks
            'columnLabelLockedByOther', 'timeLockedByOther', 'ms0TimeLockedByOther',
            // Layout helpers
            'totalCols',
            // Frequency grouping
            'freqOrder', 'locsByFreq', 'freqKeys', 'showFreqHdr',
            // CFU helpers
            'cfuNum', 'cfuTot',
            // Section footer
            'sectionConclusion', 'sectionNote',
        ));
    }
}
