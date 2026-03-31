<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEntry extends Model
{
    protected $fillable = [
        'report_id', 'report_location_id', 'period_number',
        'shift', 'analyst_id',
        'start_time', 'end_time', 'cfu_bacteria', 'cfu_fungi', 'conclusion',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function location()
    {
        return $this->belongsTo(ReportLocation::class, 'report_location_id');
    }

    public function analis()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }
}
