<?php

namespace App\Domains\ReportAssignment\Repositories;

use App\Domains\ReportAssignment\Models\ReportAssignment;
use App\Domains\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository for report assignment data access.
 */
class ReportAssignmentRepository
{
    /**
     * Get paginated report assignments for admin page.
     *
     * @return LengthAwarePaginator<int, ReportAssignment>
     */
    public function paginateForManagement(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return ReportAssignment::query()
            ->with(['reportType', 'createdBy'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get report type options for assignment form.
     *
     * @return Collection<int, ReportType>
     */
    public function reportTypeOptions(): Collection
    {
        return ReportType::query()->orderBy('annex_number')->get();
    }

    /**
     * Create report assignment.
     *
     * @param  array{report_type_id: string, product_name: string, batch_number: string, created_by: string}  $payload
     */
    public function create(array $payload): ReportAssignment
    {
        return ReportAssignment::create($payload);
    }

    /**
     * Update report assignment.
     *
     * @param  array{report_type_id: string, product_name: string, batch_number: string}  $payload
     */
    public function update(ReportAssignment $report, array $payload): ReportAssignment
    {
        $report->update($payload);

        return $report;
    }

    /**
     * Delete report assignment.
     */
    public function delete(ReportAssignment $report): void
    {
        $report->delete();
    }
}
