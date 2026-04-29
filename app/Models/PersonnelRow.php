<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelRow extends Model
{
    use HasUuids;

    protected $table = 'personnel_rows';

    protected $fillable = [
        'personnel_instance_id',
        'row_order',
        'personnel_name',
        'monitoring_time',
        'class',
        'activities',
        'filled_by',
    ];

    protected $casts = [
        'activities' => 'array',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(PersonnelInstance::class, 'personnel_instance_id');
    }

    public function filledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by');
    }

    public function samplingEntries(): HasMany
    {
        return $this->hasMany(PersonnelSamplingEntry::class, 'personnel_row_id');
    }
}
