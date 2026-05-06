<?php

namespace App\Domains\ReportType\Models;

use App\Models\ReportType as BaseReportType;

class ReportType extends BaseReportType
{
    public function sections()
    {
        return $this->hasMany(ReportSection::class)->orderBy('order');
    }
}
