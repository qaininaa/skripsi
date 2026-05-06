<?php

namespace App\Domains\AnalystReport\ReportClaiming\Repositories;

use App\Domains\AnalystReport\Models\AnalystReport;
use App\Models\Analyst;
use App\Models\Report;
use App\Models\ReportApproval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Repository for analyst report claiming and listing queries.
 */
class ReportClaimingRepository
{
    /**
     * Get returned approval row for the given report.
     *
     * @param Report $report
     * @return ReportApproval|null
     */
    public function getReturnedApproval(Report $report): ?ReportApproval
    {
        return ReportApproval::where('report_id', $report->id)
            ->where('status', 'returned')
            ->with('user')
            ->first();
    }

    /**
     * Claim report to current analyst when allowed by status and lock state.
     *
     * @param Report $report
     * @param string $userId
     * @return void
     */
    public function claimReport(Report $report, string $userId): void
    {
        if (in_array($report->status, ['pending', 'returned'], true)
            || ($report->status === 'monitoring' && $report->locked_by === null)) {
            $report->update(['status' => 'monitoring', 'locked_by' => $userId]);

            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id' => $userId,
                'type' => 'monitoring',
            ]);

            return;
        }

        if ($report->status === 'reading' && $report->locked_by === null) {
            $report->update(['locked_by' => $userId]);

            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id' => $userId,
                'type' => 'reading',
            ]);
        }
    }

    /**
     * Build base analyst query by visible statuses and return rules.
     *
     * @param string $userId
     * @param array<int, string> $visibleStatuses
     * @return Builder
     */
    public function baseAnalystQuery(string $userId, array $visibleStatuses): Builder
    {
        return AnalystReport::query()
            ->whereIn('status', $visibleStatuses)
            ->where(function (Builder $query) use ($userId): void {
                $query->where('status', '!=', 'returned')
                    ->orWhereHas('approvals', function (Builder $approvalQuery) use ($userId): void {
                        $approvalQuery->where('status', 'returned')
                            ->where('returned_to_user_id', $userId);
                    });
            });
    }

    /**
     * Get status counters for analyst listing tabs.
     *
     * @param string $userId
     * @param array<int, string> $visibleStatuses
     * @return Collection
     */
    public function statusCounts(string $userId, array $visibleStatuses): Collection
    {
        return $this->baseAnalystQuery($userId, $visibleStatuses)
            ->get(['status'])
            ->groupBy('status')
            ->map->count();
    }

    /**
     * Get paginated report listing for analyst dashboard.
     *
     * @param string $userId
     * @param array<int, string> $visibleStatuses
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAnalyst(string $userId, array $visibleStatuses, string $status, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseAnalystQuery($userId, $visibleStatuses)
            ->with(['reportType', 'approvals.user', 'lockedByUser'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            if ($status === 'submitted') {
                $query->whereIn('status', ['submitted', 'pending_manager']);
            } else {
                $query->where('status', $status);
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
