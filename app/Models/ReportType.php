<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'annex_number',
    ];

    public function sections()
    {
        return $this->hasMany(ReportSection::class)->orderBy('order');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
