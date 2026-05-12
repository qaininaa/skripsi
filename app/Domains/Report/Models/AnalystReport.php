<?php

namespace App\Domains\Report\Models;

use App\Domains\Report\Models\Report as BaseReport;

/**
 * Domain model alias for report flow.
 */
class AnalystReport extends BaseReport
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
