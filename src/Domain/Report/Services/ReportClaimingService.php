<?php

namespace Domain\Report\Services;

use App\Services\Reports\ReportViewService;
use Domain\Report\Dtos\ReportClaimingFilterDto;
use Domain\Report\Interfaces\ReportClaimingRepositoryInterface;
use Domain\Report\Models\AnalystReport;
use Domain\Report\Models\ReportApproval;
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
        private ReportClaimingRepositoryInterface $repository,
        private ReportViewService $viewService,
    ) {}

    /**
     * Build listing data for analyst dashboard.
     *
     * @return array{items: mixed, status: string, counts: Collection}
     */
    public function listingData(string $userId, ReportClaimingFilterDto $filter): array
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
     * @return array<string, mixed>
     */
    public function editViewData(AnalystReport $report, string $userId): array
    {
        $returnedApproval = $this->repository->getReturnedApproval($report);

        $isReturnedToAnotherAnalyst = $report->status === 'returned'
            && $returnedApproval
            && $returnedApproval->returned_to_user_id !== null
            && $returnedApproval->returned_to_user_id !== $userId;

        if (! $isReturnedToAnotherAnalyst) {
            $this->repository->claimReport($report, $userId);
            $report->refresh();
        }

        $this->viewService->loadRelations($report);

        $isEditable = in_array($report->status, ['monitoring', 'reading'], true)
            && (string) $report->locked_by === (string) $userId;

        $reviewerReturnedApproval = ReportApproval::where('report_id', $report->id)
            ->whereIn('step', [2, 3])
            ->where('status', 'returned')
            ->where(function ($query) use ($userId): void {
                $query->whereNull('returned_to_user_id')
                    ->orWhere('returned_to_user_id', $userId);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        $isRevision = $reviewerReturnedApproval !== null;

        $myTypes = $report->analysts
            ->where('user_id', $userId)
            ->pluck('type')
            ->map(fn ($type) => strtolower((string) $type))
            ->unique()
            ->values();

        $hasMonitoringRole = $myTypes->contains('monitoring');
        $hasReadingRole = $myTypes->contains('reading');

        $revisionActionMode = 'default';

        if ($isRevision && $isEditable) {
            if ($report->status === 'monitoring' && $hasMonitoringRole && $hasReadingRole) {
                $revisionActionMode = 'switch_to_reading';
            } elseif (($hasMonitoringRole xor $hasReadingRole) || $report->status === 'reading') {
                $revisionActionMode = 'submit_revision_only';
            }
        }

        $viewData = $this->viewService->buildViewData($report, $isEditable);

        return array_merge(
            [
                'forbidden' => false,
                'report' => $report,
                'returnedApproval' => $returnedApproval,
                'canViewReturnedNotes' => ! $isReturnedToAnotherAnalyst,
                'isRevision' => $isRevision,
                'revisionActionMode' => $revisionActionMode,
            ],
            $viewData
        );
    }

    private function normalizeStatusFilter(string $requestedStatus): string
    {
        if ($requestedStatus !== 'all' && ! in_array($requestedStatus, self::ANALYST_VISIBLE_STATUSES, true)) {
            return 'all';
        }

        return $requestedStatus;
    }
}
