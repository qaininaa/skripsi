<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportLocation extends Model
{
    use HasUuids;

    public const FREQUENCY_LABELS = [
        'operational' => 'Operasional',
        'daily' => 'Harian',
        'weekly' => 'Mingguan',
        'monthly' => 'Bulanan',
        'semi_annual' => '6 Bulan',
    ];

    protected $table = 'locations';

    protected $fillable = [
        'section_id', 'section_assigned_at', 'room_id', 'frequency', 'location_number', 'measurement_type',
        'alert_limit_total', 'alert_limit_fungi',
        'alert_action_total', 'alert_action_fungi',
    ];

    protected $casts = [
        'frequency' => 'string',
        'section_assigned_at' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(ReportSection::class, 'section_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public static function frequencyLabel(?string $frequency): string
    {
        if (! $frequency) {
            return 'Tidak Ditentukan';
        }

        return self::FREQUENCY_LABELS[$frequency] ?? ucfirst(str_replace('_', ' ', $frequency));
    }

    public function getFrequencyLabel(): string
    {
        return self::frequencyLabel($this->frequency);
    }

    public function getFormattedMeasurementType(): string
    {
        return match ($this->measurement_type) {
            'settle_plate' => 'Settle Plate',
            'air_sampler' => 'Air Sampler',
            'contact_plate' => 'Contact Plate',
            'swab' => 'Swab',
            default => ucfirst(str_replace('_', ' ', $this->measurement_type ?? '-')),
        };
    }
}
