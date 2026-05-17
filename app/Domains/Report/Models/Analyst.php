<?php

namespace App\Domains\Report\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat siapa saja analis yang mengerjakan sebuah laporan.
 * Satu laporan bisa dikerjakan beberapa analis,
 * dengan type 'monitoring' (pengukuran) atau 'reading' (pembacaan koloni).
 */
class Analyst extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'user_id',
        'type',
    ];

    /**
     * Laporan yang dikerjakan oleh analis ini.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * User (analis) yang mengerjakan laporan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
