<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLocation extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'id_room', 'frequency_id', 'location_number', 'measurement_type',
        'alert_limit_bacteria', 'alert_limit_fungi',
        'alert_action_bacteria', 'alert_action_fungi',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }
}
