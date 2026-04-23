<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Mewakili satu incubator yang dipakai dalam satu laporan.
 * Setiap laporan bisa punya lebih dari satu incubator
 * (suhu berbeda, misalnya 30°C dan 35°C).
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
        'calibration_date' => 'date',
        'due_date_calibration' => 'date',
        'date_in' => 'date',
        'date_out' => 'date',
    ];

    /**
     * Laporan yang menggunakan incubator ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Analis yang memasukkan sampel ke incubator (incubated_by).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incubatedBy()
    {
        return $this->belongsTo(User::class, 'incubated_by');
    }

    /**
     * Analis yang mengeluarkan sampel dari incubator (removed_by).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function removedBy()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }
}
