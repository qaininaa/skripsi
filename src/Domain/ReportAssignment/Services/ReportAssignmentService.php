<?php

namespace Domain\ReportAssignment\Services;

use App\Services\SectionInstanceService;
use Domain\ReportAssignment\Dtos\CreateReportAssignmentDto;
use Domain\ReportAssignment\Dtos\UpdateReportAssignmentDto;
use Domain\ReportAssignment\Interfaces\ReportAssignmentRepositoryInterface;
use Domain\ReportAssignment\Models\ReportAssignment;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Handles business logic for admin report assignment management.
 */
class ReportAssignmentService
{
    public function __construct(
        private ReportAssignmentRepositoryInterface $repository,
        private SectionInstanceService $sectionInstanceService,
    ) {}

    /**
     * Retrieve paginated report assignments for management page.
     *
     * @return LengthAwarePaginator<int, ReportAssignment>
     */
    public function paginateForManagement(?string $search, ?string $status): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $status, 15);
    }

    /**
     * Get report type options for assignment form.
     *
     * @return Collection<int, ReportType>
     */
    public function reportTypeOptions(): Collection
    {
        return $this->repository->reportTypeOptions();
    }

    /**
     * Create a report assignment and initialize its section instances.
     */
    public function createAssignment(CreateReportAssignmentDto $dto, string $createdBy): ReportAssignment
    {
        $report = $this->repository->create($dto, $createdBy);

        $this->sectionInstanceService->ensureInstancesInitialized($report);

        return $report;
    }

    /**
     * Update an existing assignment and re-sync its section instances.
     */
    public function updateAssignment(ReportAssignment $report, UpdateReportAssignmentDto $dto): ReportAssignment
    {
        $updated = $this->repository->update($report, $dto);

        $this->sectionInstanceService->ensureInstancesInitialized($updated->fresh());

        return $updated;
    }

    /**
     * Delete an assignment only if its status is still pending.
     */
    public function deletePendingAssignment(ReportAssignment $report): bool
    {
        if ($report->status !== 'pending') {
            return false;
        }

        $this->repository->delete($report);

        return true;
    }
}
