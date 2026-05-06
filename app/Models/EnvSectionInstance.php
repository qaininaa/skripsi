<?php

namespace App\Models;

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

    // ── RELATIONS ─────────────────────────────────────────────────────────────

    /**
     * Report yang punya instance ini.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Lokasi yang diukur pada instance ini.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(ReportLocation::class, 'location_id');
    }

    /**
     * Backward-compatible alias.
     */
    public function reportSection(): BelongsTo
    {
        return $this->location();
    }

    /**
     * Instance parent — NULL kalau ini adalah original.
     */
    public function parentInstance(): BelongsTo
    {
        return $this->belongsTo(EnvSectionInstance::class, 'parent_instance_id');
    }

    /**
     * Semua duplikat dari instance ini.
     * Hanya bermakna kalau dipanggil dari instance original.
     */
    public function duplicates(): HasMany
    {
        return $this->hasMany(EnvSectionInstance::class, 'parent_instance_id');
    }

    /**
     * Semua entry CFU yang dimiliki instance ini.
     */
    public function environmentalEntries(): HasMany
    {
        return $this->hasMany(ReportEnvironmentalEntry::class);
    }

    /**
     * Semua tanda tangan untuk instance ini.
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(SectionSignature::class, 'env_section_instance_id');
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    /**
     * Cek apakah instance ini adalah original (bukan duplikat).
     */
    public function isOriginal(): bool
    {
        return $this->parent_instance_id === null;
    }

    /**
     * Cek apakah instance ini adalah duplikat.
     */
    public function isDuplicate(): bool
    {
        return $this->parent_instance_id !== null;
    }

    /**
     * Ambil id original — kalau ini duplikat return parent_instance_id,
     * kalau ini original return id sendiri.
     */
    public function getOriginalId(): string
    {
        return $this->parent_instance_id ?? $this->id;
    }
}
