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
}
