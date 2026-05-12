<?php

namespace App\Domains\Report\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldLock extends Model
{
    use HasUuids;

    protected $table = 'field_locks';

    protected $fillable = [
        'table_name',
        'row_id',
        'field_name',
        'filled_by',
        'filled_at',
    ];

    protected $casts = [
        'filled_at' => 'datetime',
    ];

    public function filledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by');
    }
}
