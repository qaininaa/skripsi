<?php

namespace Domain\Report\Models;

/**
 * Domain model alias for report entry save flow.
 */
class EntryReport extends Report
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
