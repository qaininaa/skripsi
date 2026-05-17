<?php

namespace App\Domains\ReportAssignment\Services;

use App\Domains\ReportAssignment\DTOs\ReportAssignmentDTO;
use App\Domains\ReportAssignment\Models\ReportAssignment;
use App\Domains\ReportAssignment\Repositories\ReportAssignmentRepository;
use App\Services\SectionInstanceService;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service for admin report assignment use-cases.
 */
class ReportAssignmentService
{
    public function __construct(
        private ReportAssignmentRepository $repository,
        private SectionInstanceService $sectionInstanceService,
    ) {}

    /**
     * Get paginated report assignments.
     *
     * @return LengthAwarePaginator<int, ReportAssignment>
     */
    public function paginateForManagement(?string $search, ?string $status): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $status, 15);
    }

    /**
     * Get report type options.
     *
     * @return Collection<int, ReportType>
     */
    public function reportTypeOptions(): Collection
    {
        return $this->repository->reportTypeOptions();
    }

    /**
     * Create a report assignment and initialize section instances.
     */
    public function createAssignment(ReportAssignmentDTO $dto, string $createdBy): ReportAssignment
    {
        $report = $this->repository->create($dto->toCreatePayload($createdBy));

        $this->sectionInstanceService->ensureInstancesInitialized($report);

        return $report;
    }

    /**
     * Update assignment and re-sync section instances.
     */
    public function updateAssignment(ReportAssignment $report, ReportAssignmentDTO $dto): ReportAssignment
    {
        $updated = $this->repository->update($report, $dto->toUpdatePayload());

        $this->sectionInstanceService->ensureInstancesInitialized($updated->fresh());

        return $updated;
    }

    /**
     * Delete assignment only if status is still pending.
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
