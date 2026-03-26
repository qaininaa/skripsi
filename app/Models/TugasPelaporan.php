<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasPelaporan extends Model
{
    protected $table = 'tugas_pelaporan';

    protected $fillable = [
        'tanggal',
        'shift1_analis_id',
        'shift2_analis_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function shift1Analis()
    {
        return $this->belongsTo(User::class, 'shift1_analis_id');
    }

    public function shift2Analis()
    {
        return $this->belongsTo(User::class, 'shift2_analis_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
