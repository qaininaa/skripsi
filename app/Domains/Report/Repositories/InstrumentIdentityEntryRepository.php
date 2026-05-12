<?php

namespace App\Domains\Report\Repositories;

use App\Domains\Report\Models\InstrumentIdentityEntry;
use App\Domains\Report\Models\Report;

/**
 * Repository for instrument identity write operations.
 */
class InstrumentIdentityEntryRepository
{
    public function findOrCreateForReportTool(Report $report, string $toolName): InstrumentIdentityEntry
    {
        return InstrumentIdentityEntry::query()->firstOrCreate(
            [
                'report_id' => $report->id,
                'tool_name' => $toolName,
            ],
            [
                'tool_name' => $toolName,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateFields(InstrumentIdentityEntry $entry, array $payload): InstrumentIdentityEntry
    {
        if ($payload === []) {
            return $entry;
        }

        $entry->fill($payload);

        if ($entry->isDirty()) {
            $entry->save();
        }

        return $entry->refresh();
    }
}
