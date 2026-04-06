<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSection extends Model
{
    protected $table = 'sections';

    protected $fillable = [
        'report_type_id', 'name', 'slug', 'measurement_unit',
        'measurement_type', 'max_exposure', 'order',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function locations()
    {
        return $this->belongsToMany(ReportLocation::class, 'report_section', 'id_section', 'id_location')
                    ->withPivot('id')
                    ->withTimestamps()
                    ->orderBy('report_section.id');
    }
}
