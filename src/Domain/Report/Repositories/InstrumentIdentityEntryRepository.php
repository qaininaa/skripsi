<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\InstrumentIdentityEntryRepositoryInterface;
use Domain\Report\Models\InstrumentIdentityEntry;
use Domain\Report\Models\Report;

/**
 * Eloquent implementation of InstrumentIdentityEntryRepositoryInterface.
 */
class InstrumentIdentityEntryRepository implements InstrumentIdentityEntryRepositoryInterface
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
