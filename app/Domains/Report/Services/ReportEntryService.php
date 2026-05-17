<?php

namespace App\Domains\Report\Services;

use App\Domains\Report\Models\Analyst;
use App\Domains\Report\Models\Report;
use App\Domains\Report\Models\ReportSectionColumn;
use App\Domains\Report\Models\ReportSectionNote;
use Illuminate\Http\Request;

/**
 * Report entry orchestration in Report domain.
 */
class ReportEntryService
{
    public function __construct(
        private IncubatorEntryService $incubatorEntryService,
        private InstrumentIdentityEntryService $instrumentIdentityEntryService,
        private MediumEntryService $mediumEntryService,
        private EnvironmentalEntryService $environmentalEntryService,
    ) {}

    /**
     * Save full report form payload.
     *
     * @return array{0: array}
     */
    public function process(Request $request, Report $report): array
    {
        $this->instrumentIdentityEntryService->saveFromRequest($request, $report);
        $this->mediumEntryService->saveFromRequest($request, $report);
        $this->incubatorEntryService->saveFromRequest($request, $report);

        $this->saveAnalysts($request, $report);
        $this->saveSectionColumnNames($request, $report);
        $this->saveSectionNotes($request, $report);

        $savedSectionIds = [];
        [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot] =
            $this->environmentalEntryService->buildSectionLocationMaps($report);
        $instanceLookup = $this->environmentalEntryService->buildInstanceLookup($report);

        $settleTimes = $request->input('settle_times', []);
        $swabTimes = $request->input('swab_times', []);
        $exposureTimes = $request->input('exposure_times', []);

        $savedSectionIds = $this->environmentalEntryService->saveSettleTimes(
            $settleTimes,
            $report,
            $sectionLocations,
            $instanceLookup,
            $savedSectionIds
        );
        $savedSectionIds = $this->environmentalEntryService->saveSwabTimes(
            $swabTimes,
            $report,
            $sectionLocations,
            $instanceLookup,
            $savedSectionIds
        );
        $savedSectionIds = $this->environmentalEntryService->saveExposureTimes(
            $exposureTimes,
            $report,
            $sectionLocations,
            $instanceLookup,
            $savedSectionIds
        );

        $savedSectionIds = $this->environmentalEntryService->saveCfuEntries(
            $request,
            $report,
            $locationSectionType,
            $locationSectionId,
            $locationSectionTimeSlot,
            $instanceLookup,
            $savedSectionIds
        );

        return [$savedSectionIds];
    }

    public function buildSectionLocationMaps(Report $report): array
    {
        return $this->environmentalEntryService->buildSectionLocationMaps($report);
    }

    public function buildInstanceLookup(Report $report): array
    {
        return $this->environmentalEntryService->buildInstanceLookup($report);
    }

    public function saveReviewTimesToEntries(
        array $settleTimes,
        array $swabTimes,
        array $exposureTimes,
        Report $report
    ): void {
        $this->environmentalEntryService->saveReviewTimesToEntries(
            $settleTimes,
            $swabTimes,
            $exposureTimes,
            $report
        );
    }

    public function saveSectionNotes(Request $request, Report $report): void
    {
        $sectionNotes = $request->input('section_notes', []);
        if (! is_array($sectionNotes) || empty($sectionNotes)) {
            return;
        }

        foreach ($sectionNotes as $sectionId => $instanceData) {
            if (! is_array($instanceData) || empty($instanceData)) {
                continue;
            }

            $firstKey = array_key_first($instanceData);
            $isFlatNote = $firstKey !== null && ! is_array($instanceData[$firstKey]);
            if ($isFlatNote) {
                $instanceData = [1 => $instanceData];
            }

            foreach ($instanceData as $instanceNum => $noteData) {
                if (! is_array($noteData)) {
                    continue;
                }

                $instanceNumber = max(1, (int) $instanceNum);
                $notes = is_string($noteData['notes'] ?? null) ? trim($noteData['notes']) : null;
                $conclusionRaw = is_string($noteData['conclusion'] ?? null)
                    ? strtoupper(trim($noteData['conclusion']))
                    : null;
                $conclusion = in_array($conclusionRaw, ['MS', 'TMS'], true) ? $conclusionRaw : null;

                \App\Domains\Report\Models\ReportSectionNote::updateOrCreate(
                    [
                        'report_id' => (string) $report->id,
                        'section_id' => (string) $sectionId,
                        'instance_number' => $instanceNumber,
                    ],
                    [
                        'notes' => $notes !== '' ? $notes : null,
                        'conclusion' => $conclusion,
                    ]
                );
            }
        }
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

                    \App\Domains\Report\Models\ReportSectionColumn::updateOrCreate(
                        [
                            'report_id' => (string) $report->id,
                            'section_id' => (string) $sectionId,
                            'instance_number' => $instanceNumber,
                            'period_number' => $periodNumber,
                        ],
                        ['label' => $value !== '' ? $value : null]
                    );
                }
            }
        }
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
}
