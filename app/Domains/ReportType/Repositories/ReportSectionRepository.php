<?php

namespace App\Domains\ReportType\Repositories;

use App\Domains\ReportType\Models\ReportSection;
use App\Domains\ReportType\Models\ReportType;

/**
 * Repository for report section persistence operations.
 */
class ReportSectionRepository
{
    /**
     * Calculate next sort order for sections in a report type.
     */
    public function nextOrder(ReportType $reportType): int
    {
        return (int) (($reportType->sections()->max('order') ?? 0) + 1);
    }

    /**
     * Create a report section record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): ReportSection
    {
        return ReportSection::create($payload);
    }

    /**
     * Update a report section record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function update(ReportSection $section, array $payload): ReportSection
    {
        $section->update($payload);

        return $section;
    }

    /**
     * Delete a report section record.
     */
    public function delete(ReportSection $section): void
    {
        $section->delete();
    }
}
