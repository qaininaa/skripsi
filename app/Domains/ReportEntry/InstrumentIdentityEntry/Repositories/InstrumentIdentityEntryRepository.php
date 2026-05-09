<?php

namespace App\Domains\ReportEntry\InstrumentIdentityEntry\Repositories;

use App\Domains\ReportEntry\InstrumentIdentityEntry\DTOs\InstrumentIdentityEntryData;
use App\Domains\ReportEntry\InstrumentIdentityEntry\Models\InstrumentIdentityEntry;
use App\Models\Report;

/**
 * Repository for instrument identity write operations.
 */
class InstrumentIdentityEntryRepository
{
    public function upsertForReport(Report $report, InstrumentIdentityEntryData $data): InstrumentIdentityEntry
    {
        return InstrumentIdentityEntry::query()->updateOrCreate(
            [
                'report_id' => $report->id,
                'tool_name' => $data->toolName,
            ],
            [
                'no_id' => $data->noId,
                'calibration_date' => $data->calibrationDate,
                'due_date' => $data->dueDate,
            ]
        );
    }
}
