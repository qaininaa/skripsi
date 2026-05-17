<?php

namespace Domain\ReportAssignment\Repositories;

use Domain\ReportAssignment\Dtos\CreateReportAssignmentDto;
use Domain\ReportAssignment\Dtos\UpdateReportAssignmentDto;
use Domain\ReportAssignment\Interfaces\ReportAssignmentRepositoryInterface;
use Domain\ReportAssignment\Models\ReportAssignment;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of ReportAssignmentRepositoryInterface.
 */
class ReportAssignmentRepository implements ReportAssignmentRepositoryInterface
{
    /**
     * Get paginated report assignments with optional search and status filters.
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
     * Get report type options ordered by annex number.
     *
     * @return Collection<int, ReportType>
     */
    public function reportTypeOptions(): Collection
    {
        return ReportType::query()->orderBy('annex_number')->get();
    }

    /**
     * Persist a new report assignment record.
     */
    public function create(CreateReportAssignmentDto $dto, string $createdBy): ReportAssignment
    {
        return ReportAssignment::create($dto->toArray($createdBy));
    }

    /**
     * Update an existing report assignment record.
     */
    public function update(ReportAssignment $report, UpdateReportAssignmentDto $dto): ReportAssignment
    {
        $report->update($dto->toArray());

        return $report;
    }

    /**
     * Delete a report assignment record.
     */
    public function delete(ReportAssignment $report): void
    {
        $report->delete();
    }
}
