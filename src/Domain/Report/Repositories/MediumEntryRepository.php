<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\MediumEntryRepositoryInterface;
use Domain\Report\Models\MediumEntry;
use Domain\Report\Models\Report;

/**
 * Eloquent implementation of MediumEntryRepositoryInterface.
 */
class MediumEntryRepository implements MediumEntryRepositoryInterface
{
    public function findOrCreateForReportMedium(Report $report, string $name, string $mediumId): MediumEntry
    {
        return MediumEntry::query()->firstOrCreate(
            [
                'report_id' => $report->id,
                'name' => $name,
            ],
            [
                'medium_id' => $mediumId,
                'name' => $name,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateFields(MediumEntry $entry, array $payload): MediumEntry
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
