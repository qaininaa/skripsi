<?php

namespace App\Domains\ReportType\Models;

use Domain\Location\Models\Location;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReportSection extends Model
{
    use HasUuids;

    protected $table = 'sections';

    protected $fillable = [
        'report_type_id', 'measurement_unit',
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

    public function getMeasurementKeyAttribute(): string
    {
        return self::normalizeMeasurementType($this->measurement_type);
    }

    public static function normalizeMeasurementType(?string $value): string
    {
        $raw = Str::of((string) $value)
            ->lower()
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->squish()
            ->value();

        return match ($raw) {
            'settle plate' => 'settle_plate',
            'air sampler' => 'air_sampler',
            'contact plate' => 'contact_plate',
            'swab' => 'swab',
            default => Str::of($raw)->replace(' ', '_')->value(),
        };
    }

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'section_id')
            ->orderBy('section_assigned_at')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
