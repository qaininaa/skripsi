<?php

namespace App\Domains\ReportEntry\MediumEntry\Repositories;

use App\Domains\ReportEntry\MediumEntry\Models\MediumEntry;
use App\Models\Report;

/**
 * Repository for medium identity write operations.
 */
class MediumEntryRepository
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
