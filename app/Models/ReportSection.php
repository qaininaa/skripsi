<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSection extends Model
{
    use HasUuids;

    protected $table = 'sections';

    protected $fillable = [
        'report_type_id', 'name', 'measurement_unit',
        'measurement_type', 'max_column', 'column_label',
        'time_slot_type', 'has_machine_setup', 'has_shift_toggle',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'has_machine_setup' => 'boolean',
            'has_shift_toggle' => 'boolean',
        ];
    }

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ReportLocation::class, 'section_id')
            ->orderBy('id');
    }
}
