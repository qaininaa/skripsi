<?php

namespace Domain\Report\Models;

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

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
