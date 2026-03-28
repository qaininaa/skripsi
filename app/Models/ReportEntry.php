<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEntry extends Model
{
    protected $fillable = [
        'report_id', 'report_location_id', 'period_number',
        'shift', 'analis_id',
        'jam_mulai', 'jam_selesai', 'cfu_bacteria', 'cfu_fungi', 'conclusion',
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
        return $this->belongsTo(User::class, 'analis_id');
    }
}
