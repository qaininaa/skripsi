<?php

namespace Domain\ReportType\Services;

use Domain\AuditLog\Services\AuditLogService;
use Domain\Location\Models\Location;
use Domain\ReportType\Dtos\CreateReportTypeDto;
use Domain\ReportType\Dtos\UpdateReportTypeDto;
use Domain\ReportType\Interfaces\ReportTypeRepositoryInterface;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service for report type aggregate management use-cases.
 */
class ReportTypeService
{
    public function __construct(
        private ReportTypeRepositoryInterface $repository,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Get paginated report types for management page.
     *
     * @return LengthAwarePaginator<int, ReportType>
     */
    public function paginateForManagement(): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement(15);
    }

    /**
     * Find duplicate report type for store flow.
     */
    public function findDuplicate(CreateReportTypeDto $dto, ?string $ignoreReportTypeId = null): ?ReportType
    {
        return $this->repository->findDuplicate(
            $dto->sopCode,
            $dto->sopVersion,
            $dto->annexNumber,
            $ignoreReportTypeId,
        );
    }

    /**
     * Create report type including related medium and incubator rows.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function createReportType(CreateReportTypeDto $dto, array $meta): ReportType
    {
        $reportType = $this->repository->create($dto->toBasePayload());

        $this->syncMediumAndIncubators($reportType, $dto->mediumLabels, $dto->incubators);

        $this->auditLogService->log(
            'create_report_type',
            "Membuat jenis laporan: {$reportType->name} ({$reportType->annex_number})",
            $meta,
        );

        return $reportType;
    }

    /**
     * Update report type and synchronize related medium/incubator rows.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function updateReportType(ReportType $reportType, UpdateReportTypeDto $dto, array $meta): ReportType
    {
        $reportType = $this->repository->update($reportType, $dto->toBasePayload());

        $this->repository->clearMediumAndIncubatorTypes($reportType);
        $this->syncMediumAndIncubators($reportType, $dto->mediumLabels, $dto->incubators);

        $this->auditLogService->log(
            'update_report_type',
            "Memperbarui jenis laporan: {$reportType->name} ({$reportType->annex_number})",
            $meta,
        );

        return $reportType;
    }

    /**
     * Delete report type and write audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function deleteReportType(ReportType $reportType, array $meta): void
    {
        $name = $reportType->name;
        $annex = $reportType->annex_number;

        $this->repository->delete($reportType);

        $this->auditLogService->log(
            'delete_report_type',
            "Menghapus jenis laporan: {$name} ({$annex})",
            $meta,
        );
    }

    /**
     * Check whether report type has related reports.
     */
    public function hasReports(ReportType $reportType): bool
    {
        return $this->repository->hasReports($reportType);
    }

    /**
     * Get ordered location list for report type show page.
     *
     * @return Collection<int, Location>
     */
    public function locationsForShow(): Collection
    {
        return $this->repository->locationsForShow();
    }

    /**
     * Persist medium and incubator rows for a report type.
     *
     * @param  array<int, string>  $mediumLabels
     * @param  array<int, \Domain\ReportType\Dtos\IncubatorTypeDto>  $incubators
     */
    private function syncMediumAndIncubators(ReportType $reportType, array $mediumLabels, array $incubators): void
    {
        foreach ($mediumLabels as $label) {
            $this->repository->addMediumType($reportType, $label);
        }

        foreach ($incubators as $incubator) {
            $this->repository->addIncubatorType($reportType, $incubator->label, $incubator->minDay);
        }
    }
}
