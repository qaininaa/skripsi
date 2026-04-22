<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportEnvironmentalEntry extends Model
{
    use HasUuids;

    protected $table = 'report_environmental_entries';

    protected $fillable = [
        'report_id', 'report_section_id', 'instance_number', 'period_number',
        'shift', 'analyst_id',
        'start_time', 'end_time', 'cfu_bacteria', 'cfu_fungi',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function section()
    {
        return $this->belongsTo(ReportSection::class, 'report_section_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function personnel()
    {
        return $this->hasMany(ReportPersonnel::class, 'report_environmental_entry_id');
    }
}
