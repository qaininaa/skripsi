<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\ReportClaimingRepositoryInterface;
use Domain\Report\Models\Analyst;
use Domain\Report\Models\AnalystReport;
use Domain\Report\Models\EnvSectionInstance;
use Domain\Report\Models\Incubator;
use Domain\Report\Models\IncubatorEntry;
use Domain\Report\Models\InstrumentIdentityEntry;
use Domain\Report\Models\MediumEntry;
use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Domain\Report\Models\ReportEnvironmentalEntry;
use Domain\Report\Models\ReportSectionColumn;
use Domain\ReportType\Models\ReportSection;
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
        //    Catatan: medium tipe swab tidak punya gpt_number (field disembunyikan di view)
        $mediumEmpty = ! MediumEntry::where('report_id', $reportId)->exists();

        $mediumIncomplete = false;
        if (! $mediumEmpty) {
            // Non-swab: name, batch_number, gpt_number, expiration_date wajib
            $nonSwabIncomplete = MediumEntry::where('report_id', $reportId)
                ->whereRaw('LOWER(name) NOT LIKE ?', ['%swab%'])
                ->where(function (Builder $q): void {
                    $q->whereNull('name')
                        ->orWhereNull('batch_number')
                        ->orWhereNull('gpt_number')
                        ->orWhereNull('expiration_date');
                })
                ->exists();

            // Swab: name, batch_number, expiration_date wajib (gpt_number tidak ada)
            $swabIncomplete = MediumEntry::where('report_id', $reportId)
                ->whereRaw('LOWER(name) LIKE ?', ['%swab%'])
                ->where(function (Builder $q): void {
                    $q->whereNull('name')
                        ->orWhereNull('batch_number')
                        ->orWhereNull('expiration_date');
                })
                ->exists();

            $mediumIncomplete = $nonSwabIncomplete || $swabIncomplete;
        }

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

    /**
     * Check whether all required CFU entries are filled before submitting to supervisor.
     *
     * Rules:
     * - For every EnvSectionInstance in this report (original + duplicates):
     *   - For every non-optional location (location_number does NOT start with '*)'):
     *     - For every period that has a time entry (start_time not null):
     *       - cfu_bacteria AND cfu_fungi must both be non-null.
     *
     * @return array{ready: bool, missing: string[]}
     */
    public function checkSubmitReadiness(string $reportId): array
    {
        $missing = [];

        // Load all instances with their location (including section info)
        $instances = EnvSectionInstance::where('report_id', $reportId)
            ->with(['location.section', 'location.room'])
            ->get();

        if ($instances->isEmpty()) {
            return ['ready' => true, 'missing' => []];
        }

        foreach ($instances as $instance) {
            $location = $instance->location;
            if (! $location) {
                continue;
            }

            // Skip optional locations (location_number starts with '*)')
            if (str_starts_with((string) ($location->location_number ?? ''), '*)')) {
                continue;
            }

            $section = $location->section;
            if (! $section) {
                continue;
            }

            $maxColumn = (int) $section->max_column;
            if ($maxColumn < 1) {
                continue;
            }

            for ($period = 1; $period <= $maxColumn; $period++) {
                // Only check periods that have a time entry (start_time not null)
                // — periods without time are N/A and don't need CFU
                $entry = ReportEnvironmentalEntry::where('report_id', $reportId)
                    ->where('env_section_instance_id', $instance->id)
                    ->where('period_number', $period)
                    ->whereNotNull('start_time')
                    ->first(['cfu_bacteria', 'cfu_fungi']);

                if (! $entry) {
                    continue; // No time entry → N/A, skip
                }

                if ($entry->cfu_bacteria === null || $entry->cfu_fungi === null) {
                    $roomName = $location->room->room_name ?? 'Ruangan tidak diketahui';
                    $locNum   = $location->location_number ?? '-';
                    $missing[] = "CFU belum lengkap: {$roomName} (No. Lokasi {$locNum}), Exposure {$period}";
                }
            }
        }

        return [
            'ready'   => empty($missing),
            'missing' => $missing,
        ];
    }
}
