<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    protected $fillable = [
        'code', 'name', 'annex_number', 'instrument',
        'frequency', 'medium_groups', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'medium_groups' => 'array',
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
