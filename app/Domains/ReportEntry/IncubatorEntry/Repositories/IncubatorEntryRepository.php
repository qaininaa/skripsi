<?php

namespace App\Domains\ReportEntry\IncubatorEntry\Repositories;

use App\Domains\ReportEntry\IncubatorEntry\Models\Incubator;
use App\Domains\ReportEntry\IncubatorEntry\Models\IncubatorEntry;
use App\Models\Report;

/**
 * Repository for incubator write operations.
 */
class IncubatorEntryRepository
{
    public function findOrCreateIncubatorForReportType(Report $report, string $reportTypeIncubatorId): Incubator
    {
        return Incubator::query()->firstOrCreate(
            [
                'report_id' => $report->id,
                'report_type_incubator_id' => $reportTypeIncubatorId,
            ],
            [
                'report_id' => $report->id,
                'report_type_incubator_id' => $reportTypeIncubatorId,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateIncubatorFields(Incubator $incubator, array $payload): Incubator
    {
        if ($payload === []) {
            return $incubator;
        }

        $incubator->fill($payload);

        if ($incubator->isDirty()) {
            $incubator->save();
        }

        return $incubator->refresh();
    }

    public function findOrCreateIncubatorMediumEntry(Incubator $incubator, string $mediumType): IncubatorEntry
    {
        return IncubatorEntry::query()->firstOrCreate(
            [
                'incubator_id' => $incubator->id,
                'medium_type' => $mediumType,
            ],
            [
                'incubator_id' => $incubator->id,
                'medium_type' => $mediumType,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateIncubatorEntryFields(IncubatorEntry $entry, array $payload): IncubatorEntry
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
