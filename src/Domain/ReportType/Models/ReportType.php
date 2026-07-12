<?php

namespace Domain\ReportType\Models;

use Domain\Report\Models\Report;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Aggregate root for the ReportType domain.
 */
class ReportType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'annex_number', 'sop_code', 'sop_version',
    ];

    public function mediumTypes()
    {
        return $this->hasMany(MediumType::class);
    }

    public function media()
    {
        return $this->mediumTypes();
    }

    public function incubatorTypes()
    {
        return $this->hasMany(IncubatorType::class);
    }

    public function incubatorConfigs()
    {
        return $this->incubatorTypes();
    }

    public function sections()
    {
        return $this->hasMany(ReportSection::class)->orderBy('order');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
