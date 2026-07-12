<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frequency extends Model
{
    protected $fillable = ['name'];

    public function locations()
    {
        return $this->hasMany(ReportLocation::class, 'frequency_id');
    }
}
