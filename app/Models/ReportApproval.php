<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportApproval extends Model
{
    protected $fillable = [
        'report_id', 'step', 'role_label', 'user_id',
        'signed_at', 'paraf_path', 'status', 'catatan',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
