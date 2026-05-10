<?php

namespace App\Domains\Report\Services;

use App\Domains\Report\Services\IncubatorEntryService;
use App\Domains\Report\Services\InstrumentIdentityEntryService;
use App\Domains\Report\Services\MediumEntryService;
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
        $myShift = 1;

        $this->instrumentIdentityEntryService->saveFromRequest($request, $report);
        $this->mediumEntryService->saveFromRequest($request, $report);
        $hd = $this->saveIncubators($request, $report);

        $hd = $this->saveHeaderData($request, $hd);

        $this->saveAnalysts($request, $report);
        $this->personnelEntryService->saveSectionColumnNames($request, $report);

        $savedSectionIds = [];
        [$sectionLocations, $locationSectionType, $locationSectionId, $locationSectionTimeSlot] =
            $this->environmentalEntryService->buildSectionLocationMaps($report);
        $instanceLookup = $this->environmentalEntryService->buildInstanceLookup($report);

        $settleTimes = $request->input('settle_times', []);
        $swabTimes = $request->input('swab_times', []);
        $exposureTimes = $request->input('exposure_times', []);

        [$savedSectionIds, $hd] = $this->environmentalEntryService->saveSettleTimes(
            $settleTimes,
            $report,
            $sectionLocations,
            $instanceLookup,
            $savedSectionIds,
            $hd,
            $myShift
        );
        [$savedSectionIds, $hd] = $this->environmentalEntryService->saveSwabTimes(
            $swabTimes,
            $report,
            $sectionLocations,
            $instanceLookup,
            $savedSectionIds,
            $hd,
            $myShift
        );
        [$savedSectionIds, $hd] = $this->environmentalEntryService->saveExposureTimes(
            $exposureTimes,
            $report,
            $sectionLocations,
            $instanceLookup,
            $savedSectionIds,
            $hd,
            $myShift
        );

        if ($request->has('header_data')
            || ! empty($settleTimes) || ! empty($swabTimes) || ! empty($exposureTimes)) {
            unset($hd['_field_owners']);
            $report->update(['header_data' => $hd]);
        }

        $savedSectionIds = $this->environmentalEntryService->saveCfuEntries(
            $request,
            $report,
            $locationSectionType,
            $locationSectionId,
            $locationSectionTimeSlot,
            $instanceLookup,
            $savedSectionIds,
            $myShift
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

    private function saveIncubators(Request $request, Report $report): array
    {
        $this->incubatorEntryService->saveFromRequest($request, $report);

        return $report->header_data ?? [];
    }

    private function saveHeaderData(Request $request, array $hd): array
    {
        if (! $request->has('header_data')) {
            return $hd;
        }

        $incoming = $request->input('header_data', []);
        if (! is_array($incoming)) {
            return $hd;
        }

        unset($incoming['_field_owners']);

        foreach ($incoming as $sectionKey => $sectionData) {
            if (! is_array($sectionData)) {
                $hd[$sectionKey] = $sectionData;
                continue;
            }

            foreach ($sectionData as $fieldKey => $fieldValue) {
                $hd[$sectionKey][$fieldKey] = $fieldValue;
            }
        }

        return $hd;
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
