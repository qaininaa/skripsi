<?php

namespace App\Domains\AnalystReport\Models;

use App\Models\Report as BaseReport;

/**
 * Domain model alias for analyst report flow.
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
