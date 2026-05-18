<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\ReportClaimingRepositoryInterface;
use Domain\Report\Models\Analyst;
use Domain\Report\Models\AnalystReport;
use Domain\Report\Models\Incubator;
use Domain\Report\Models\IncubatorEntry;
use Domain\Report\Models\InstrumentIdentityEntry;
use Domain\Report\Models\MediumEntry;
use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of ReportClaimingRepositoryInterface.
 */
class ReportClaimingRepository implements ReportClaimingRepositoryInterface
{
    public function getReturnedApproval(Report $report): ?ReportApproval
    {
        return ReportApproval::where('report_id', $report->id)
            ->where('status', 'returned')
            ->with('user')
            ->first();
    }

    /**
     * Check whether all required pre-reading sections are fully filled.
     *
     * Sections checked:
     *  1. Identitas Instrumen  – instrument_entries: no_id, calibration_date, due_date
     *  2. Identitas Medium     – medium_identities: name, batch_number, gpt_number, expiration_date
     *  3. Proses Inkubasi Medium Monitoring – incubators: no_id, calibration_date, due_date_calibration
     *                                       – incubator_entries (medium_type=monitoring): incubated_by, date_in, time_in
     *
     * @return array{ready: bool, missing: string[]}
     */
    public function checkReadingReadiness(string $reportId): array
    {
        $missing = [];

        // 1. Identitas Instrumen — setidaknya satu baris harus ada dan semua kolom wajib terisi
        $instrumentIncomplete = InstrumentIdentityEntry::where('report_id', $reportId)
            ->where(function (Builder $q): void {
                $q->whereNull('no_id')
                    ->orWhereNull('calibration_date')
                    ->orWhereNull('due_date');
            })
            ->exists();

        $instrumentEmpty = ! InstrumentIdentityEntry::where('report_id', $reportId)->exists();

        if ($instrumentEmpty || $instrumentIncomplete) {
            $missing[] = 'Identitas Instrumen';
        }

        // 2. Identitas Medium — setidaknya satu baris harus ada dan semua kolom wajib terisi
        $mediumIncomplete = MediumEntry::where('report_id', $reportId)
            ->where(function (Builder $q): void {
                $q->whereNull('name')
                    ->orWhereNull('batch_number')
                    ->orWhereNull('gpt_number')
                    ->orWhereNull('expiration_date');
            })
            ->exists();

        $mediumEmpty = ! MediumEntry::where('report_id', $reportId)->exists();

        if ($mediumEmpty || $mediumIncomplete) {
            $missing[] = 'Identitas Medium';
        }

        // 3. Proses Inkubasi Medium Monitoring
        //    a) Incubator header: no_id, calibration_date, due_date_calibration harus terisi
        $incubatorIncomplete = Incubator::where('report_id', $reportId)
            ->where(function (Builder $q): void {
                $q->whereNull('no_id')
                    ->orWhereNull('calibration_date')
                    ->orWhereNull('due_date_calibration');
            })
            ->exists();

        $incubatorEmpty = ! Incubator::where('report_id', $reportId)->exists();

        //    b) IncubatorEntry untuk medium_type = monitoring harus ada dan terisi
        $incubatorIds = Incubator::where('report_id', $reportId)->pluck('id');

        $monitoringEntryIncomplete = false;
        $monitoringEntryEmpty = true;

        if ($incubatorIds->isNotEmpty()) {
            $monitoringEntryEmpty = ! IncubatorEntry::whereIn('incubator_id', $incubatorIds)
                ->where('medium_type', 'monitoring')
                ->exists();

            $monitoringEntryIncomplete = IncubatorEntry::whereIn('incubator_id', $incubatorIds)
                ->where('medium_type', 'monitoring')
                ->where(function (Builder $q): void {
                    $q->whereNull('incubated_by')
                        ->orWhereNull('date_in')
                        ->orWhereNull('time_in');
                })
                ->exists();
        }

        if ($incubatorEmpty || $incubatorIncomplete || $monitoringEntryEmpty || $monitoringEntryIncomplete) {
            $missing[] = 'Proses Inkubasi Medium Monitoring';
        }

        return [
            'ready' => empty($missing),
            'missing' => $missing,
        ];
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
     * @return LengthAwarePaginator<int, Report>
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
