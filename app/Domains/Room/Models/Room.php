<?php

namespace App\Domains\Room\Models;

use App\Domains\Location\Models\Location;
use App\Models\Room as BaseRoom;

class Room extends BaseRoom
{
    public function locations()
    {
        return $this->hasMany(Location::class, 'room_id');
    }
}
