<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['room_name', 'room_number', 'class'];

    public function locations()
    {
        return $this->hasMany(ReportLocation::class, 'id_room');
    }
}
