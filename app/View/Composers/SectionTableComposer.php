<?php

namespace App\View\Composers;

use App\Helpers\CfuHelper;
use App\Services\ReportSectionService;
use Illuminate\View\View;

class SectionTableComposer
{
    public function __construct(private ReportSectionService $sectionService) {}

    public function compose(View $view): void
    {
        $data       = $view->getData();
        $section    = $data['section'];
        $hd         = $data['hd'];
        $instance   = $data['instance'] ?? 1;
        $report     = $data['report'];
        $isEditable = $data['isEditable'] ?? false;
        $entryMap   = $data['entryMap'] ?? [];

        $maxCols   = $section->max_column;
        $romanNums = ['I', 'II', 'III', 'IV', 'V', 'VI'];
        $hdOwners  = $hd['_field_owners'] ?? [];

        // ── Time-slot type flags ──────────────────────────────────────────────
        $hasMachineSetup = (bool) $section->has_machine_setup;
        $hasTime         = $section->time_slot_type === 'single';
        $isPerLocation   = $section->time_slot_type === 'per_location';
        $isDualAB        = $section->time_slot_type === 'dual_ab';
        $isSwabTime      = $section->time_slot_type === 'swab';
        $isSettlePlate   = $section->measurement_type === 'settle_plate';
        $colLabelRaw     = is_string($section->column_label) ? trim($section->column_label) : null;
        $colLabel        = $colLabelRaw !== '' ? $colLabelRaw : null;
        $subColsPerExp   = $isPerLocation ? 4 : 3;

        // ── Phase flags ───────────────────────────────────────────────────────
        $isMonitoring = $report->status === 'monitoring';
        $isReading    = $report->status === 'reading';

        // ── Machine Set-up ownership ──────────────────────────────────────────
        $ms0Owner  = isset($hdOwners["exposure_times_{$section->id}_{$instance}_0"])
            ? (string) $hdOwners["exposure_times_{$section->id}_{$instance}_0"]
            : null;
        $ms0Locked = $isEditable && $ms0Owner !== null && $ms0Owner !== (string) auth()->id();
        $msHasTime = ! empty($hd['exposure_times'][$section->id][$instance][0]['start_time'] ?? null);

        // ── Column shift assignments ──────────────────────────────────────────
        $savedAsgn      = $hd['shift_assignments'][$section->id] ?? [];
        $secAssignments = [];
        for ($c = 1; $c <= $maxCols; $c++) {
            $secAssignments[$c] = isset($savedAsgn[$c]) ? (int) $savedAsgn[$c] : 1;
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

        // ── Total column count for colspan calculations ────────────────────────
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
        $sectionNote       = $hd['section_notes'][$section->id] ?? [];

        $view->with(compact(
            // Type flags
            'hasMachineSetup', 'hasTime', 'isPerLocation', 'isDualAB', 'isSwabTime',
            'isSettlePlate',
            'colLabel', 'subColsPerExp',
            // Phase
            'isMonitoring', 'isReading',
            // Machine set-up
            'ms0Owner', 'ms0Locked', 'msHasTime',
            // Columns
            'secAssignments', 'columnNames', 'maxCols', 'romanNums', 'hdOwners',
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
