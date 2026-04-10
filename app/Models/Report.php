<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_type_id', 'product_name', 'batch_number',
        'analyst_monitoring', 'analyst_reading',
        'status', 'created_by', 'header_data',
    ];

    protected $casts = [
        'header_data'       => 'array',
        'analyst_monitoring' => 'array',
        'analyst_reading'    => 'array',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
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
