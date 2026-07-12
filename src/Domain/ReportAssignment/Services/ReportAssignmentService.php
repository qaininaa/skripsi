<?php

namespace Domain\ReportAssignment\Services;

use App\Services\SectionInstanceService;
use Domain\AuditLog\Services\AuditLogService;
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
        private AuditLogService $auditLogService,
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
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function createAssignment(CreateReportAssignmentDto $dto, string $createdBy, array $meta = []): ReportAssignment
    {
        $report = $this->repository->create($dto, $createdBy);

        $this->sectionInstanceService->ensureInstancesInitialized($report);

        $report->loadMissing('reportType');

        $this->auditLogService->log(
            'create_report_assignment',
            "{$this->actorLabel($meta)} menambah tugas pelaporan: {$this->assignmentLabel($report)}",
            $meta
        );

        return $report;
    }

    /**
     * Update an existing assignment and re-sync its section instances.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function updateAssignment(ReportAssignment $report, UpdateReportAssignmentDto $dto, array $meta = []): ReportAssignment
    {
        $report->loadMissing('reportType');
        $oldInfo = $this->assignmentLabel($report);

        $updated = $this->repository->update($report, $dto);

        $this->sectionInstanceService->ensureInstancesInitialized($updated->fresh());

        $updated->loadMissing('reportType');

        $this->auditLogService->log(
            'update_report_assignment',
            "{$this->actorLabel($meta)} mengubah tugas pelaporan: {$oldInfo} menjadi {$this->assignmentLabel($updated)}",
            $meta
        );

        return $updated;
    }

    /**
     * Delete an assignment only if its status is still pending.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function deletePendingAssignment(ReportAssignment $report, array $meta = []): bool
    {
        if ($report->status !== 'pending') {
            return false;
        }

        $report->loadMissing('reportType');
        $assignmentInfo = $this->assignmentLabel($report);

        $this->repository->delete($report);

        $this->auditLogService->log(
            'delete_report_assignment',
            "{$this->actorLabel($meta)} menghapus tugas pelaporan: {$assignmentInfo}",
            $meta
        );

        return true;
    }

    /**
     * @param  array{actor_username?: string|null}  $meta
     */
    private function actorLabel(array $meta): string
    {
        return $meta['actor_username'] ?? 'User';
    }

    private function assignmentLabel(ReportAssignment $report): string
    {
        $annexNumber = $report->reportType?->annex_number ?? 'Annex tidak diketahui';
        $reportTypeName = $report->reportType?->name ?? 'Jenis laporan tidak diketahui';

        return "{$report->product_name} (Batch {$report->batch_number}) - {$annexNumber} {$reportTypeName}";
    }
}
