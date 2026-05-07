<?php

namespace App\Domains\AnalystReport\ReportClaiming\Services;

use App\Domains\AnalystReport\Models\AnalystReport;
use App\Domains\AnalystReport\ReportClaiming\DTOs\ReportClaimingFilterDTO;
use App\Domains\AnalystReport\ReportClaiming\Repositories\ReportClaimingRepository;
use App\Domains\AnalystReport\Shared\Services\AnalystReportEntryService;
use App\Models\ReportApproval;
use App\Services\Reports\ReportViewService;
use Illuminate\Support\Collection;

/**
 * Service for analyst report claiming and listing flow.
 */
class ReportClaimingService
{
    /**
     * @var array<int, string>
     */
    private const ANALYST_VISIBLE_STATUSES = [
        'pending',
        'monitoring',
        'reading',
        'submitted',
        'pending_manager',
        'returned',
    ];

    public function __construct(
        private ReportClaimingRepository $repository,
        private ReportViewService $viewService,
        private AnalystReportEntryService $entryService,
    ) {}

    /**
     * Build listing data for analyst dashboard.
     *
     * @param string $userId
     * @param ReportClaimingFilterDTO $filter
     * @return array{items: mixed, status: string, counts: Collection}
     */
    public function listingData(string $userId, ReportClaimingFilterDTO $filter): array
    {
        $status = $this->normalizeStatusFilter($filter->status);

        $rawCounts = $this->repository->statusCounts($userId, self::ANALYST_VISIBLE_STATUSES);

        $counts = collect([
            'pending' => $rawCounts['pending'] ?? 0,
            'monitoring' => $rawCounts['monitoring'] ?? 0,
            'reading' => $rawCounts['reading'] ?? 0,
            'submitted' => ($rawCounts['submitted'] ?? 0) + ($rawCounts['pending_manager'] ?? 0),
            'returned' => $rawCounts['returned'] ?? 0,
        ]);

        $items = $this->repository->paginateForAnalyst(
            $userId,
            self::ANALYST_VISIBLE_STATUSES,
            $status,
            15
        );

        return [
            'items' => $items,
            'status' => $status,
            'counts' => $counts,
        ];
    }

    /**
     * Claim report and build edit view data for analyst.
     *
     * @param AnalystReport $report
     * @param string $userId
     * @return array<string, mixed>
     */
    public function editViewData(AnalystReport $report, string $userId): array
    {
        $returnedApproval = null;

        if ($report->status === 'returned') {
            $returnedApproval = $this->repository->getReturnedApproval($report);

            if ($returnedApproval
                && $returnedApproval->returned_to_user_id !== null
                && $returnedApproval->returned_to_user_id !== $userId) {
                return [
                    'forbidden' => true,
                    'message' => 'Laporan ini dikembalikan ke analis lain dan tidak dapat Anda akses.',
                ];
            }
        }

        $this->repository->claimReport($report, $userId);
        $report->refresh();

        $this->entryService->migrateFieldOwners($report);

        $this->viewService->loadRelations($report);

        $isEditable = in_array($report->status, ['monitoring', 'reading'], true)
            && (string) $report->locked_by === (string) $userId;

        $isRevision = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->exists();

        $viewData = $this->viewService->buildViewData($report, $isEditable);

        return array_merge(
            [
                'forbidden' => false,
                'report' => $report,
                'returnedApproval' => $returnedApproval,
                'isRevision' => $isRevision,
            ],
            $viewData
        );
    }

    /**
     * Validate status query value.
     *
     * @param string $requestedStatus
     * @return string
     */
    private function normalizeStatusFilter(string $requestedStatus): string
    {
        if ($requestedStatus !== 'all' && ! in_array($requestedStatus, self::ANALYST_VISIBLE_STATUSES, true)) {
            return 'all';
        }

        return $requestedStatus;
    }
}
