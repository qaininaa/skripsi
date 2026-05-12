<?php

namespace App\Domains\Room\Models;

use App\Domains\Location\Models\Location;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasUuids;

    protected $fillable = ['room_name', 'room_number', 'class'];

    public function locations()
    {
        return $this->hasMany(Location::class, 'room_id');
    }
}
