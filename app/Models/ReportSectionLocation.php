<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSectionLocation extends Model
{
    use HasUuids;

    protected $table = 'report_sections';

    protected $fillable = [
        'section_id',
        'location_id',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ReportSection::class, 'section_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(ReportLocation::class, 'location_id');
    }

    public function envSectionInstances(): HasMany
    {
        return $this->hasMany(EnvSectionInstance::class, 'report_section_id');
    }

    public function environmentalEntries(): HasMany
    {
        return $this->hasMany(ReportEnvironmentalEntry::class, 'report_section_id');
    }
}
