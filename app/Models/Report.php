<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_type_id', 'product_name', 'batch_number',
        'shift1_analyst_id', 'shift2_analyst_id',
        'status', 'created_by',
    ];

    protected $casts = [];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function shift1Analis()
    {
        return $this->belongsTo(User::class, 'shift1_analyst_id');
    }

    public function shift2Analis()
    {
        return $this->belongsTo(User::class, 'shift2_analyst_id');
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
