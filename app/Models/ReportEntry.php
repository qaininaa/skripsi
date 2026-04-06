<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEntry extends Model
{
    protected $fillable = [
        'report_id', 'report_section_id', 'period_number',
        'shift', 'analyst_id',
        'start_time', 'end_time', 'cfu_bacteria', 'cfu_fungi',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function analis()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }
}
