<?php

namespace App\Domains\ReportEntry\MediumEntry\Repositories;

use App\Domains\ReportEntry\MediumEntry\Models\MediumEntry;
use App\Models\Report;

/**
 * Repository for medium identity write operations.
 */
class MediumEntryRepository
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateOrCreateByName(Report $report, string $name, array $payload): MediumEntry
    {
        return MediumEntry::query()->updateOrCreate(
            [
                'report_id' => $report->id,
                'name' => $name,
            ],
            $payload
        );
    }
}
