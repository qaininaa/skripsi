<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Mewakili satu instrumen (Air Sampler) yang digunakan dalam sebuah laporan.
 * Setiap laporan bisa punya lebih dari satu Air Sampler.
 */
class InstrumentEntry extends Model
{
    use HasUuids;

    protected $table = 'instrument_entries';

    protected $fillable = [
        'report_id',
        'tool_name',
        'no_id',
        'calibration_date',
        'due_date',
    ];

    protected $casts = [
        'calibration_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Laporan yang menggunakan instrumen ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
