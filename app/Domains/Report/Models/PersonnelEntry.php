<?php

namespace App\Domains\Report\Models;

use App\Domains\ReportType\Models\PersonnelMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'method_id',
        'personnel_instance_id',
        'personnel_name',
        'personnel_time',
        'class',
        'activities',
    ];

    protected $casts = [
        'activities' => 'array',
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(PersonnelMethod::class, 'method_id');
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(PersonnelInstance::class, 'personnel_instance_id');
    }

    public function samplingEntries(): HasMany
    {
        return $this->hasMany(PersonnelSamplingEntry::class, 'personnel_entry_id');
    }
}
