<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportSection extends Model
{
    use HasUuids;

    protected $table = 'sections';

    protected $fillable = [
        'report_type_id', 'name', 'measurement_unit',
        'measurement_type', 'max_column', 'column_label',
        'time_slot_type', 'has_machine_setup',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'has_machine_setup' => 'boolean',
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
