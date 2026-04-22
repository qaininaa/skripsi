<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportLocation extends Model
{
    use HasUuids;

    protected $table = 'locations';

    protected $fillable = [
        'room_id', 'frequency_id', 'location_number', 'measurement_type',
        'alert_limit_total', 'alert_limit_fungi',
        'alert_action_total', 'alert_action_fungi',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function frequency()
    {
        return $this->belongsTo(Frequency::class, 'frequency_id');
    }

    public function getFormattedMeasurementType(): string
    {
        return match($this->measurement_type) {
            'settle_plate' => 'Settle Plate',
            'air_sampler' => 'Air Sampler',
            'contact_plate' => 'Contact Plate',
            'swab' => 'Swab',
            default => ucfirst(str_replace('_', ' ', $this->measurement_type ?? '-')),
        };
    }
}