<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLocation extends Model
{
    protected $fillable = [
        'report_section_id', 's_no', 'room_name', 'class', 'room_number',
        'location_number', 'alert_limit_bacteria', 'action_limit_bacteria',
        'alert_limit_fungi', 'action_limit_fungi',
    ];

    public function section()
    {
        return $this->belongsTo(ReportSection::class, 'report_section_id');
    }

    public function entries()
    {
        return $this->hasMany(ReportEntry::class);
    }
}
