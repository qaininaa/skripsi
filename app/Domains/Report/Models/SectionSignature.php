<?php

namespace App\Domains\Report\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Menyimpan tanda tangan (TTD) per section laporan.
 * Setiap baris = satu user menandatangani satu section dengan role tertentu.
 *
 * Role yang tersedia:
 *  - monitoring : analis fase monitoring
 *  - reading    : analis fase pembacaan
 *  - supervisor : supervisor yang mereview
 *  - manager    : manager yang menyetujui
 */
class SectionSignature extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'section_id',
        'instance_number',
        'user_id',
        'role',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * Laporan yang memiliki TTD ini.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * User yang menandatangani section ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
