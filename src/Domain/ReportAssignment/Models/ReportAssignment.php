<?php

namespace Domain\ReportAssignment\Models;

use App\Domains\Report\Models\Report as BaseReport;
use Domain\ReportType\Models\ReportType;
use Domain\User\Models\User;

/**
 * Domain model alias for report assignment context.
 *
 * Reuses the underlying `reports` table from the Report domain while
 * exposing the relationships specific to admin assignment workflows.
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
