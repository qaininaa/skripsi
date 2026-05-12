<?php

namespace App\Domains\Report\Models;

use App\Domains\ReportType\Models\ReportSection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportSectionColumn extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'section_id',
        'instance_number',
        'period_number',
        'label',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function section()
    {
        return $this->belongsTo(ReportSection::class, 'section_id');
    }
}
