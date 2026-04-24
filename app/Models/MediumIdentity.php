<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Mewakili satu medium (agar) yang digunakan dalam satu laporan.
 * Setiap laporan bisa punya banyak medium dengan nama/batch yang berbeda.
 */
class MediumIdentity extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'medium_id',
        'name',
        'batch_number',
        'gpt_number',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    /**
     * Laporan yang menggunakan medium ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
