<?php

namespace App\Domains\Report\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelSignature extends Model
{
    use HasUuids;

    protected $fillable = ['report_id', 'user_id', 'role', 'signed_at'];

    protected $casts = ['signed_at' => 'datetime'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
