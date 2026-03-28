<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSection extends Model
{
    protected $fillable = [
        'report_type_id', 'name', 'slug', 'measurement_unit',
        'measurement_type', 'max_exposures', 'order',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function locations()
    {
        return $this->hasMany(ReportLocation::class)->orderBy('s_no');
    }
}
