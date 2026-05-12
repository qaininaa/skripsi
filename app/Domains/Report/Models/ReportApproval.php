<?php

namespace App\Domains\Report\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportApproval extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id', 'step', 'role', 'user_id',
        'signed_at', 'signature_path', 'status', 'notes', 'returned_to_user_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function returnedTo()
    {
        return $this->belongsTo(User::class, 'returned_to_user_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
