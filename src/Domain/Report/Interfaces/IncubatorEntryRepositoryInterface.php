<?php

namespace Domain\Report\Interfaces;

use Domain\Report\Models\Incubator;
use Domain\Report\Models\IncubatorEntry;
use Domain\Report\Models\Report;

/**
 * Contract for incubator persistence operations.
 */
interface IncubatorEntryRepositoryInterface
{
    public function findOrCreateIncubatorForReportType(Report $report, string $reportTypeIncubatorId): Incubator;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateIncubatorFields(Incubator $incubator, array $payload): Incubator;

    public function findOrCreateIncubatorMediumEntry(Incubator $incubator, string $mediumType): IncubatorEntry;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateIncubatorEntryFields(IncubatorEntry $entry, array $payload): IncubatorEntry;
}
