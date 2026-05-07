<?php

namespace App\Domains\ReportEntry\Models;

use App\Models\Report as BaseReport;

/**
 * Domain model alias for report entry save flow.
 */
class EntryReport extends BaseReport
{
    /**
     * @var string
     */
    protected $table = 'reports';

    /**
     * Keep relationship foreign key naming compatible with reports table.
     */
    public function getForeignKey(): string
    {
        return 'report_id';
    }
}
