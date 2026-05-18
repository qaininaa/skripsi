<?php

namespace Domain\Report\Interfaces;

use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Contract for analyst report claiming and listing queries.
 */
interface ReportClaimingRepositoryInterface
{
    public function getReturnedApproval(Report $report): ?ReportApproval;

    /**
     * Check whether all required pre-reading fields are filled for a report.
     *
     * Returns an array with:
     *   - 'ready'   => bool
     *   - 'missing' => string[]  (list of human-readable missing section names)
     */
    public function checkReadingReadiness(string $reportId): array;

    public function claimReport(Report $report, string $userId): void;

    /**
     * @param array<int, string> $visibleStatuses
     */
    public function baseAnalystQuery(string $userId, array $visibleStatuses): Builder;

    /**
     * @param array<int, string> $visibleStatuses
     */
    public function statusCounts(string $userId, array $visibleStatuses): Collection;

    /**
     * @param array<int, string> $visibleStatuses
     * @return LengthAwarePaginator<int, Report>
     */
    public function paginateForAnalyst(string $userId, array $visibleStatuses, string $status, int $perPage = 15): LengthAwarePaginator;
}
