<?php

namespace Domain\Report\Interfaces;

use Domain\Report\Models\MediumEntry;
use Domain\Report\Models\Report;

/**
 * Contract for medium identity persistence operations.
 */
interface MediumEntryRepositoryInterface
{
    public function findOrCreateForReportMedium(Report $report, string $name, string $mediumId): MediumEntry;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateFields(MediumEntry $entry, array $payload): MediumEntry;
}
