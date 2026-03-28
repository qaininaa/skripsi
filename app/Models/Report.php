<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_type_id', 'tanggal', 'nama_produk', 'nomor_batch_produk',
        'shift1_analis_id', 'shift2_analis_id',
        'status', 'header_data', 'created_by',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'header_data' => 'array',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function shift1Analis()
    {
        return $this->belongsTo(User::class, 'shift1_analis_id');
    }

    public function shift2Analis()
    {
        return $this->belongsTo(User::class, 'shift2_analis_id');
    }

    public function entries()
    {
        return $this->hasMany(ReportEntry::class);
    }

    public function approvals()
    {
        return $this->hasMany(ReportApproval::class)->orderBy('step');
    }

    public function currentApprovalStep()
    {
        return $this->approvals()->where('status', 'pending')->orderBy('step')->first();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
