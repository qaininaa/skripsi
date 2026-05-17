<?php

namespace Domain\ReportType\Repositories;

use Domain\ReportType\Interfaces\ReportSectionRepositoryInterface;
use Domain\ReportType\Models\ReportSection;
use Domain\ReportType\Models\ReportType;

/**
 * Eloquent implementation of ReportSectionRepositoryInterface.
 */
class ReportSectionRepository implements ReportSectionRepositoryInterface
{
    /**
     * Calculate next sort order for sections in a report type.
     */
    public function nextOrder(ReportType $reportType): int
    {
        return (int) (($reportType->sections()->max('order') ?? 0) + 1);
    }

    /**
     * Persist a report section record.
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
