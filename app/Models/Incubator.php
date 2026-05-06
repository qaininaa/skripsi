<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Mewakili satu incubator (perangkat) yang dipakai dalam satu laporan.
 * Setiap laporan bisa punya lebih dari satu incubator (suhu berbeda).
 * Data tracking masuk/keluar per jenis medium ada di incubator_entries.
 */
class Incubator extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'report_type_incubator_id',
        'no_id',
        'calibration_date',
        'due_date_calibration',
    ];

    protected $casts = [
        'calibration_date'     => 'date',
        'due_date_calibration' => 'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function incubatorType(): BelongsTo
    {
        return $this->belongsTo(IncubatorType::class, 'report_type_incubator_id');
    }

    public function reportTypeIncubator(): BelongsTo
    {
        return $this->incubatorType();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IncubatorEntry::class);
    }
}
