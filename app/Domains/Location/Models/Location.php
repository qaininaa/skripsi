<?php

namespace App\Domains\Location\Models;

use App\Domains\Room\Models\Room;
use App\Models\ReportLocation as BaseLocation;
use App\Models\ReportSection;

class Location extends BaseLocation
{
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function section()
    {
        return $this->belongsTo(ReportSection::class, 'section_id');
    }
}
