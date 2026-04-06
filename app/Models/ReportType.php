<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    protected $fillable = [
        'code', 'name', 'annex_number', 'instrument',
        'medium_groups', 'incubators',
    ];

    protected $casts = [
        'medium_groups' => 'array',
        'incubators'    => 'array',
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
