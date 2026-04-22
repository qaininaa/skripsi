<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasUuids;

    protected $fillable = ['room_name', 'room_number', 'class'];

    public function locations()
    {
        return $this->hasMany(ReportLocation::class, 'room_id');
    }
}
