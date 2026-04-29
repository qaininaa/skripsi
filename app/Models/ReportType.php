<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'annex_number', 'sop_code', 'sop_version', 'has_personnel',
    ];

    public function media()
    {
        return $this->hasMany(ReportTypeMedium::class);
    }

    public function incubatorConfigs()
    {
        return $this->hasMany(ReportTypeIncubator::class);
    }

    public function sections()
    {
        return $this->hasMany(ReportSection::class)->orderBy('order');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function personnelMethods()
    {
        return $this->hasMany(PersonnelMethod::class);
    }
}
