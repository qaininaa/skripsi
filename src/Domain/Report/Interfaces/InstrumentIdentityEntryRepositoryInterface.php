<?php

namespace Domain\Report\Interfaces;

use Domain\Report\Models\InstrumentIdentityEntry;
use Domain\Report\Models\Report;

/**
 * Contract for instrument identity persistence operations.
 */
interface InstrumentIdentityEntryRepositoryInterface
{
    public function findOrCreateForReportTool(Report $report, string $toolName): InstrumentIdentityEntry;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateFields(InstrumentIdentityEntry $entry, array $payload): InstrumentIdentityEntry;
}
