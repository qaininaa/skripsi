<?php

namespace Domain\ReportType\Interfaces;

use Domain\ReportType\Models\ReportSection;
use Domain\ReportType\Models\ReportType;

/**
 * Contract for report section persistence operations.
 */
interface ReportSectionRepositoryInterface
{
    /**
     * Calculate next sort order for sections in a report type.
     */
    public function nextOrder(ReportType $reportType): int;

    /**
     * Persist a report section record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): ReportSection;

    /**
     * Update a report section record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function update(ReportSection $section, array $payload): ReportSection;

    /**
     * Delete a report section record.
     */
    public function delete(ReportSection $section): void;
}
