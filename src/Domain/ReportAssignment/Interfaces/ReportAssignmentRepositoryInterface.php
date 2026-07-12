<?php

namespace Domain\ReportAssignment\Interfaces;

use Domain\ReportAssignment\Dtos\CreateReportAssignmentDto;
use Domain\ReportAssignment\Dtos\UpdateReportAssignmentDto;
use Domain\ReportAssignment\Models\ReportAssignment;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contract for report assignment data access.
 */
interface ReportAssignmentRepositoryInterface
{
    /**
     * Get paginated report assignments for admin management page.
     *
     * @return LengthAwarePaginator<int, ReportAssignment>
     */
    public function paginateForManagement(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get report type options for assignment form dropdown.
     *
     * @return Collection<int, ReportType>
     */
    public function reportTypeOptions(): Collection;

    /**
     * Persist a new report assignment from DTO.
     */
    public function create(CreateReportAssignmentDto $dto, string $createdBy): ReportAssignment;

    /**
     * Update an existing report assignment from DTO.
     */
    public function update(ReportAssignment $report, UpdateReportAssignmentDto $dto): ReportAssignment;

    /**
     * Delete a report assignment record.
     */
    public function delete(ReportAssignment $report): void;
}
