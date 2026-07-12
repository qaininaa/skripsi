<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSection extends Model
{
    protected $table = 'sections';

    protected $fillable = [
        'report_type_id', 'name', 'measurement_unit',
        'measurement_type', 'max_exposure', 'column_label',
        'time_slot_type', 'has_shared_time', 'has_shift_toggle',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'has_shared_time'  => 'boolean',
            'has_shift_toggle' => 'boolean',
        ];
    }

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
