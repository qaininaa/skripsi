<?php

namespace Domain\Room\Models;

use App\Domains\Location\Models\Location;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Room model representing a master room record.
 */
class Room extends Model
{
    use HasUuids;

    protected $fillable = ['room_name', 'room_number', 'class'];

    public function locations()
    {
        return $this->hasMany(Location::class, 'room_id');
    }
}
