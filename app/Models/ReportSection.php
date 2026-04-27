<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    /**
     * Relasi pivot section-lokasi (table: report_sections).
     */
    public function reportSections(): HasMany
    {
        return $this->hasMany(ReportSectionLocation::class, 'section_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(ReportLocation::class, 'report_sections', 'section_id', 'location_id')
            ->withPivot('id')
            ->withTimestamps()
            ->orderBy('report_sections.id');
    }
}
