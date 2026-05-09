<?php

namespace App\Domains\ReportType\Services;

use App\Domains\Location\Models\Location;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Repositories\ReportTypeRepository;
use App\Models\AuditLog;
use App\Services\Personnels\PersonnelService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service for report type aggregate management use-cases.
 */
class ReportTypeService
{
    public function __construct(
        private ReportTypeRepository $repository,
        private PersonnelService $personnelService,
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
     * Find duplicate report type by SOP code, SOP version, and annex number.
     *
     * @param  array<string, mixed>  $validated
     */
    public function findDuplicate(array $validated, ?string $ignoreReportTypeId = null): ?ReportType
    {
        return $this->repository->findDuplicate($validated, $ignoreReportTypeId);
    }

    /**
     * Create report type including related medium and incubator rows.
     *
     * @param array{
     *   sop_code: string,
     *   sop_version: string,
     *   name: string,
     *   annex_number: int,
     *   has_personnel?: bool,
     *   medium_labels?: array<int, string|null>,
     *   incubator_labels?: array<int, string|null>,
     *   incubator_min_days?: array<int, int|string|null>
     * } $validated
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function create(array $validated, array $meta): ReportType
    {
        $reportType = $this->repository->create([
            'sop_code' => $validated['sop_code'],
            'sop_version' => $validated['sop_version'],
            'name' => $validated['name'],
            'annex_number' => $validated['annex_number'],
            'has_personnel' => $validated['has_personnel'] ?? false,
        ]);

        $this->syncMedia($reportType, $validated['medium_labels'] ?? []);
        $this->syncIncubators($reportType, $validated['incubator_labels'] ?? [], $validated['incubator_min_days'] ?? []);

        $this->auditLog('create_report_type', "Membuat jenis laporan: {$reportType->name} ({$reportType->annex_number})", $meta);

        return $reportType;
    }

    /**
     * Update report type and synchronize related medium/incubator rows.
     *
     * @param array{
     *   sop_code: string,
     *   sop_version: string,
     *   name: string,
     *   annex_number: int,
     *   has_personnel?: bool,
     *   medium_labels?: array<int, string|null>,
     *   incubator_labels?: array<int, string|null>,
     *   incubator_min_days?: array<int, int|string|null>
     * } $validated
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function update(ReportType $reportType, array $validated, array $meta): ReportType
    {
        $wasPersonnel = (bool) $reportType->has_personnel;
        $isPersonnel = (bool) ($validated['has_personnel'] ?? false);

        $reportType = $this->repository->update($reportType, [
            'sop_code' => $validated['sop_code'],
            'sop_version' => $validated['sop_version'],
            'name' => $validated['name'],
            'annex_number' => $validated['annex_number'],
            'has_personnel' => $isPersonnel,
        ]);

        $this->repository->clearMediumAndIncubatorTypes($reportType);
        $this->syncMedia($reportType, $validated['medium_labels'] ?? []);
        $this->syncIncubators($reportType, $validated['incubator_labels'] ?? [], $validated['incubator_min_days'] ?? []);

        if (! $wasPersonnel && $isPersonnel) {
            $this->personnelService->generate($reportType);
        } elseif ($wasPersonnel && ! $isPersonnel) {
            $this->personnelService->remove($reportType);
        }

        $this->auditLog('update_report_type', "Memperbarui jenis laporan: {$reportType->name} ({$reportType->annex_number})", $meta);

        return $reportType;
    }

    /**
     * Delete report type and write audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function delete(ReportType $reportType, array $meta): void
    {
        $name = $reportType->name;
        $annex = $reportType->annex_number;

        $this->repository->delete($reportType);

        $this->auditLog('delete_report_type', "Menghapus jenis laporan: {$name} ({$annex})", $meta);
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
     * Synchronize medium labels into medium type rows.
     *
     * @param  array<int, string|null>  $labels
     */
    private function syncMedia(ReportType $reportType, array $labels): void
    {
        foreach ($labels as $label) {
            $label = trim((string) $label);
            if ($label !== '') {
                $this->repository->addMediumType($reportType, $label);
            }
        }
    }

    /**
     * Synchronize incubator labels and minimum days into incubator type rows.
     *
     * @param  array<int, string|null>  $labels
     * @param  array<int, int|string|null>  $minDays
     */
    private function syncIncubators(ReportType $reportType, array $labels, array $minDays): void
    {
        foreach ($labels as $index => $label) {
            $label = trim((string) $label);
            if ($label !== '') {
                $this->repository->addIncubatorType(
                    $reportType,
                    $label,
                    (int) ($minDays[$index] ?? 3)
                );
            }
        }
    }

    /**
     * Write audit log for report type action.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    private function auditLog(string $action, string $description, array $meta): void
    {
        AuditLog::create([
            'user_id' => $meta['user_id'] ?? null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $meta['ip_address'] ?? null,
            'user_agent' => $meta['user_agent'] ?? null,
        ]);
    }
}
