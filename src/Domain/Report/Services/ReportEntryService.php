<?php

namespace Domain\Report\Services;

use Domain\Report\Interfaces\FieldLockRepositoryInterface;
use Domain\Report\Interfaces\ReportEntryRepositoryInterface;
use Domain\Report\Models\Analyst;
use Domain\Report\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        private ReportEntryRepositoryInterface $repository,
        private FieldLockRepositoryInterface $fieldLockRepository,
    ) {}

    /**
     * Save full report form payload.
     *
     * @return array{0: array<string, bool>}
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

    /**
     * @return array{0: array<string, array<int, array<string, mixed>>>, 1: array<string, string>, 2: array<string, string>, 3: array<string, string>}
     */
    public function buildSectionLocationMaps(Report $report): array
    {
        return $this->environmentalEntryService->buildSectionLocationMaps($report);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function buildInstanceLookup(Report $report): array
    {
        return $this->environmentalEntryService->buildInstanceLookup($report);
    }

    /**
     * @param  array<string, mixed>  $settleTimes
     * @param  array<string, mixed>  $swabTimes
     * @param  array<string, mixed>  $exposureTimes
     */
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

                $this->repository->upsertSectionNote(
                    (string) $report->id,
                    (string) $sectionId,
                    $instanceNumber,
                    $notes !== '' ? $notes : null,
                    $conclusion
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

        $userId = (string) Auth::id();
        $lockTable = 'report_section_columns';

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
                    $value = $value !== '' ? $value : null;

                    // Find existing row to check/acquire lock
                    $existingRow = $this->repository->findSectionColumn(
                        (string) $report->id,
                        (string) $sectionId,
                        $instanceNumber,
                        $periodNumber
                    );

                    // If a row already exists, enforce lock before allowing overwrite
                    if ($existingRow !== null && ! empty($existingRow->label)) {
                        $canWrite = $this->fieldLockRepository->acquireOrOwned(
                            $lockTable,
                            (string) $existingRow->id,
                            'label',
                            $userId
                        );

                        if (! $canWrite) {
                            // Terkunci oleh analis lain — lewati
                            continue;
                        }

                        if ($value === null) {
                            $this->fieldLockRepository->releaseIfOwned(
                                $lockTable,
                                (string) $existingRow->id,
                                'label',
                                $userId
                            );
                        }
                    }

                    $this->repository->upsertSectionColumn(
                        (string) $report->id,
                        (string) $sectionId,
                        $instanceNumber,
                        $periodNumber,
                        $value
                    );

                    // Setelah upsert, acquire lock jika baru diisi
                    if ($value !== null) {
                        $savedRow = $this->repository->findSectionColumn(
                            (string) $report->id,
                            (string) $sectionId,
                            $instanceNumber,
                            $periodNumber
                        );
                        if ($savedRow !== null) {
                            $this->fieldLockRepository->acquireOrOwned(
                                $lockTable,
                                (string) $savedRow->id,
                                'label',
                                $userId
                            );
                        }
                    }
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
