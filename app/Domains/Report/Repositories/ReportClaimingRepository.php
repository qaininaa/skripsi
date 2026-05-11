<?php

namespace App\Domains\Report\Repositories;

use App\Domains\Report\Models\AnalystReport;
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
    public function getReturnedApproval(Report $report): ?ReportApproval
    {
        return ReportApproval::where('report_id', $report->id)
            ->where('status', 'returned')
            ->with('user')
            ->first();
    }

    public function claimReport(Report $report, string $userId): void
    {
        if (in_array($report->status, ['pending'], true)
            || ($report->status === 'monitoring' && $report->locked_by === null)) {
            $report->update(['status' => 'monitoring', 'locked_by' => $userId]);

            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id' => $userId,
                'type' => 'monitoring',
            ]);

            return;
        }

        if ($report->status === 'returned' && $report->locked_by === null) {
            $targetStatus = $this->resolveReturnedTargetStatus($report, $userId);

            $report->update(['status' => $targetStatus, 'locked_by' => $userId]);

            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id' => $userId,
                'type' => $targetStatus,
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

    private function resolveReturnedTargetStatus(Report $report, string $userId): string
    {
        $types = Analyst::query()
            ->where('report_id', $report->id)
            ->where('user_id', $userId)
            ->pluck('type')
            ->map(fn ($type) => strtolower((string) $type))
            ->unique();

        $hasMonitoring = $types->contains('monitoring');
        $hasReading = $types->contains('reading');

        if ($hasMonitoring && ! $hasReading) {
            return 'monitoring';
        }

        if ($hasReading && ! $hasMonitoring) {
            return 'reading';
        }

        if ($hasMonitoring && $hasReading) {
            return 'monitoring';
        }

        return 'monitoring';
    }

    /**
     * @param array<int, string> $visibleStatuses
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
     * @param array<int, string> $visibleStatuses
     */
    public function statusCounts(string $userId, array $visibleStatuses): Collection
    {
        return $this->baseAnalystQuery($userId, $visibleStatuses)
            ->get(['status'])
            ->groupBy('status')
            ->map->count();
    }

    /**
     * @param array<int, string> $visibleStatuses
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
