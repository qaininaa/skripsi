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
        'incubated_by',
        'date_in',
        'time_in',
        'removed_by',
        'date_out',
        'time_out',
    ];

    protected $casts = [
        'calibration_date'     => 'date',
        'due_date_calibration' => 'date',
        'date_in'              => 'date',
        'date_out'             => 'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function reportTypeIncubator(): BelongsTo
    {
        return $this->belongsTo(ReportTypeIncubator::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IncubatorEntry::class);
    }
}
