<?php

namespace Domain\ReportType\Interfaces;

use Domain\Location\Models\Location;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contract for report type aggregate persistence and queries.
 */
interface ReportTypeRepositoryInterface
{
    /**
     * Get paginated report types for management page.
     *
     * @return LengthAwarePaginator<int, ReportType>
     */
    public function paginateForManagement(int $perPage = 10): LengthAwarePaginator;

    /**
     * Find duplicate report type by SOP code, version, and annex number.
     */
    public function findDuplicate(string $sopCode, string $sopVersion, int $annexNumber, ?string $ignoreReportTypeId = null): ?ReportType;

    /**
     * Persist a new report type record.
     *
     * @param  array{sop_code: string, sop_version: string, name: string, annex_number: int}  $payload
     */
    public function create(array $payload): ReportType;

    /**
     * Update an existing report type record.
     *
     * @param  array{sop_code: string, sop_version: string, name: string, annex_number: int}  $payload
     */
    public function update(ReportType $reportType, array $payload): ReportType;

    /**
     * Delete a report type record.
     */
    public function delete(ReportType $reportType): void;

    /**
     * Check whether report type already has report instances.
     */
    public function hasReports(ReportType $reportType): bool;

    /**
     * Remove related medium type and incubator type rows for a report type.
     */
    public function clearMediumAndIncubatorTypes(ReportType $reportType): void;

    /**
     * Persist a medium type row for a report type.
     */
    public function addMediumType(ReportType $reportType, string $label): void;

    /**
     * Persist an incubator type row for a report type.
     */
    public function addIncubatorType(ReportType $reportType, string $label, int $minDay): void;

    /**
     * Fetch ordered locations list used by the report type show page.
     *
     * @return Collection<int, Location>
     */
    public function locationsForShow(): Collection;
}
