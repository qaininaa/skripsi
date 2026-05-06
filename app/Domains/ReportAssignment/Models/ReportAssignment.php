<?php

namespace App\Domains\ReportAssignment\Models;

use App\Domains\ReportType\Models\ReportType;
use App\Domains\User\Models\User;
use App\Models\Report as BaseReport;

/**
 * Domain model alias for report assignment context.
 */
class ReportAssignment extends BaseReport
{
    protected $table = 'reports';

    public function reportType()
    {
        return $this->belongsTo(ReportType::class, 'report_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function printedByUser()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
