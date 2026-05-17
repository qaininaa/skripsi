<?php

namespace App\Domains\Report\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportEnvironmentalEntry extends Model
{
    use HasUuids;

    protected $table = 'report_environmental_entries';

    protected $fillable = [
        'report_id', 'env_section_instance_id',
        'period_number', 'shift', 'analyst_id',
        'start_time', 'end_time', 'cfu_bacteria', 'cfu_fungi',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function envSectionInstance()
    {
        return $this->belongsTo(EnvSectionInstance::class, 'env_section_instance_id');
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
