<?php

namespace App\Services;

use App\Helpers\CfuHelper;
use App\Models\EnvSectionInstance;
use App\Models\Report;
use App\Models\ReportSection;
use Illuminate\Support\Collection;

/**
 * Centralises reusable business logic for report-section views.
 * Keeps controllers and blade templates thin.
 */
class ReportSectionService
{
    /**
     * Build the entryMap from a report's loaded environmentalEntries.
     * Shape: entryMap[$pivot_id][$instance][$period_number][$shift] = entry
     */
    public function buildEntryMap(Report $report): array
    {
        // Build ordered instance lookup: instance_id → {pivot_id, instance_number}
        // Ordering: original first (parent_instance_id IS NULL), then duplicates by created_at/id
        $instances = EnvSectionInstance::where('report_id', $report->id)
            ->orderByRaw('report_section_id, CASE WHEN parent_instance_id IS NULL THEN 0 ELSE 1 END, created_at, id')
            ->get();

        $countByPivot = [];
        $instanceMap  = []; // instance_id → ['pivot_id', 'num']
        foreach ($instances as $inst) {
            $pivotId = (string) $inst->report_section_id;
            $countByPivot[$pivotId] = ($countByPivot[$pivotId] ?? 0) + 1;
            $instanceMap[(string) $inst->id] = [
                'pivot_id' => $pivotId,
                'num'      => $countByPivot[$pivotId],
            ];
        }

        $entryMap = [];
        foreach ($report->environmentalEntries as $entry) {
            $inst = $instanceMap[(string) $entry->env_section_instance_id] ?? null;
            if (! $inst) {
                continue;
            }
            $entryMap[$inst['pivot_id']][$inst['num']][$entry->period_number][$entry->shift] = $entry;
        }
        return $entryMap;
    }

    /**
     * Expand report sections, accounting for duplicate counts.
     * Each element: ['section', 'instance', 'totalInstances', 'secNum']
     * secNum starts at 5 (first 4 fixed sections: info, alat, medium, inkubator).
     */
    public function buildSectionInstances(Report $report): array
    {
        $report->loadMissing('reportType.sections.reportSections');

        $pivotIds = $report->reportType->sections
            ->flatMap(fn ($section) => $section->reportSections->pluck('id'))
            ->unique()
            ->values();

        $countsByPivot  = collect();
        // instanceIdsByPivot: pivot_id → [1 => uuid, 2 => uuid, ...]
        $instanceIdsByPivot = [];
        if ($pivotIds->isNotEmpty()) {
            $instances = EnvSectionInstance::query()
                ->where('report_id', $report->id)
                ->whereIn('report_section_id', $pivotIds->all())
                ->orderByRaw('report_section_id, CASE WHEN parent_instance_id IS NULL THEN 0 ELSE 1 END, created_at, id')
                ->get(['id', 'report_section_id']);

            $countMap = [];
            foreach ($instances as $inst) {
                $pid = (string) $inst->report_section_id;
                $countMap[$pid] = ($countMap[$pid] ?? 0) + 1;
                $instanceIdsByPivot[$pid][$countMap[$pid]] = (string) $inst->id;
            }
            $countsByPivot = collect($countMap);
        }

        $sectionInstances = [];

        foreach ($report->reportType->sections as $section) {
            $sectionPivotIds = $section->reportSections->pluck('id');
            $count = (int) ($sectionPivotIds
                ->map(fn ($pivotId) => (int) ($countsByPivot[(string) $pivotId] ?? 0))
                ->max() ?? 0);
            $count = max(1, $count);

            // Find the first pivot_id that has instances, to look up UUIDs
            $representativePivotId = (string) ($sectionPivotIds->first(
                fn ($pid) => isset($instanceIdsByPivot[(string) $pid])
            ) ?? $sectionPivotIds->first());

            for ($i = 1; $i <= $count; $i++) {
                $sectionInstances[] = [
                    'section'        => $section,
                    'instance'       => $i,
                    'instance_id'    => $instanceIdsByPivot[$representativePivotId][$i] ?? null,
                    'totalInstances' => $count,
                    // secNum: 1-indexed offset by 4 fixed sections above the table
                    'secNum'         => count($sectionInstances) + 5,
                ];
            }
        }

        return $sectionInstances;
    }

    /**
     * Determine which optional section groups a report needs to display.
     * Returns keys: needsAirSampler, needsInkubator, needsMedium.
     */
    public function computeSectionNeeds(Report $report): array
    {
        $types = $report->reportType->sections->pluck('measurement_type')->unique();

        return [
            'needsAirSampler' => $types->contains('air_sampler'),
            'needsInkubator'  => $types->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty(),
            'needsMedium'     => $types->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty(),
        ];
    }

    /**
     * Collect all CFU entries for one location across all columns and shifts.
     */
    public function collectLocationEntries(string $pivotId, int $instance, int $maxColumn, array $entryMap): Collection
    {
        $entries = collect();
        for ($p = 0; $p <= $maxColumn; $p++) {
            for ($s = 1; $s <= 2; $s++) {
                if (isset($entryMap[$pivotId][$instance][$p][$s])) {
                    $entries->push($entryMap[$pivotId][$instance][$p][$s]);
                }
            }
        }
        return $entries;
    }

    /**
     * Compute the conclusion label for a single location row.
     * Returns 'TMS', 'Alert', 'MS', or null when there is no data.
     */
    public function computeRowConclusion($loc, Collection $locEntries): ?string
    {
        $hasCfuEntries = $locEntries->contains(
            fn ($e) => $e->cfu_bacteria !== null || $e->cfu_fungi !== null
        );

        if (! $hasCfuEntries) {
            return null;
        }

        $maxT = $locEntries->max(
            fn ($e) => (CfuHelper::toInt($e->cfu_bacteria) ?? 0) + (CfuHelper::toInt($e->cfu_fungi) ?? 0)
        ) ?? 0;

        $maxF = $locEntries->max(
            fn ($e) => CfuHelper::toInt($e->cfu_fungi) ?? 0
        ) ?? 0;

        $hasTMS = ($loc->alert_action_total && $maxT >= $loc->alert_action_total)
               || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi);

        if ($hasTMS) {
            return 'TMS';
        }

        $hasAlt = ($loc->alert_limit_total && $maxT >= $loc->alert_limit_total)
               || ($loc->alert_limit_fungi && $maxF >= $loc->alert_limit_fungi);

        return $hasAlt ? 'Alert' : 'MS';
    }

    /**
     * Compute the overall conclusion for a section across all its instances.
     * Returns 'TMS', 'MS', or null when there is no data at all.
     */
    public function computeSectionConclusion(ReportSection $section, array $entryMap, int $totalInstances): ?string
    {
        $sectionHasTMS   = false;
        $sectionAllEmpty = true;

        for ($inst = 1; $inst <= $totalInstances; $inst++) {
            foreach ($section->locations as $loc) {
                $locEntries = $this->collectLocationEntries(
                    $loc->pivot->id, $inst, $section->max_column, $entryMap
                );

                $cfuEntries = $locEntries->filter(
                    fn ($e) => $e->cfu_bacteria !== null || $e->cfu_fungi !== null
                );

                if ($cfuEntries->isEmpty()) {
                    continue;
                }

                $sectionAllEmpty = false;

                $maxT = $cfuEntries->max(
                    fn ($e) => (CfuHelper::toInt($e->cfu_bacteria) ?? 0) + (CfuHelper::toInt($e->cfu_fungi) ?? 0)
                ) ?? 0;

                $maxF = $cfuEntries->max(
                    fn ($e) => CfuHelper::toInt($e->cfu_fungi) ?? 0
                ) ?? 0;

                if (($loc->alert_action_total && $maxT >= $loc->alert_action_total)
                    || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi)) {
                    $sectionHasTMS = true;
                }
            }
        }

        return $sectionAllEmpty ? null : ($sectionHasTMS ? 'TMS' : 'MS');
    }
}
