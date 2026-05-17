<?php

namespace Domain\Report\Models;

use Domain\Location\Models\Location;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * EnvSectionInstance
 *
 * Merepresentasikan satu kali pengerjaan lokasi dalam sebuah report.
 * Normal = 1 instance per lokasi (parent_instance_id = NULL).
 * Duplikat = instance baru dengan parent_instance_id = id original.
 *
 * @property string      $id
 * @property string      $report_id
 * @property string      $location_id
 * @property string|null $parent_instance_id
 * @property string|null $reason
 */
class EnvSectionInstance extends Model
{
    use HasUuids;

    protected $table = 'env_section_instances';

    protected $fillable = [
        'report_id',
        'location_id',
        'parent_instance_id',
        'reason',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Backward-compatible alias.
     */
    public function reportSection(): BelongsTo
    {
        return $this->location();
    }

    public function parentInstance(): BelongsTo
    {
        return $this->belongsTo(EnvSectionInstance::class, 'parent_instance_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(EnvSectionInstance::class, 'parent_instance_id');
    }

    public function environmentalEntries(): HasMany
    {
        return $this->hasMany(ReportEnvironmentalEntry::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(SectionSignature::class, 'env_section_instance_id');
    }

    public function isOriginal(): bool
    {
        return $this->parent_instance_id === null;
    }

    public function isDuplicate(): bool
    {
        return $this->parent_instance_id !== null;
    }

    public function getOriginalId(): string
    {
        return $this->parent_instance_id ?? $this->id;
    }
}
