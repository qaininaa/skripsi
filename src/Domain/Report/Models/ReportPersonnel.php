<?php

namespace Domain\Report\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportPersonnel extends Model
{
    use HasUuids;

    protected $table = 'report_personnel';

    protected $fillable = [
        'report_environmental_entry_id',
        'nama_personal',
        'jam_pemantauan',
        'aktivitas',
        'titik_sampling',
        'kelas',
        'hasil_pengamatan_b',
        'hasil_pengamatan_f',
        'hasil_pengamatan_t',
        'kesimpulan',
    ];

    protected $casts = [
        'aktivitas' => 'array',
        'jam_pemantauan' => 'datetime:H:i',
    ];

    public function entry()
    {
        return $this->belongsTo(ReportEnvironmentalEntry::class, 'report_environmental_entry_id');
    }
}
