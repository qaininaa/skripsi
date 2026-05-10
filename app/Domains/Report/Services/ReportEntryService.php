<?php

namespace App\Domains\Report\Services;

use App\Models\Analyst;
use App\Models\Report;
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
        private PersonnelEntryService $personnelEntryService,
    ) {}

    /**
     * Save full report form payload.
     *
     * @return array{0: array, 1: bool}
     */
    public function process(Request $request, Report $report): array
    {
        $this->instrumentIdentityEntryService->saveFromRequest($request, $report);
        $this->mediumEntryService->saveFromRequest($request, $report);
        $this->incubatorEntryService->saveFromRequest($request, $report);

        $this->saveAnalysts($request, $report);
        $this->personnelEntryService->saveSectionColumnNames($request, $report);
        $this->personnelEntryService->saveSectionNotes($request, $report);

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

        $hasPersonnelData = false;
        if ($request->has('personnel') || $request->has('page_notes')) {
            $hasPersonnelData = $this->personnelEntryService->savePersonnel($request, $report);
        }

        return [$savedSectionIds, $hasPersonnelData];
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
        $this->personnelEntryService->saveSectionNotes($request, $report);
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
