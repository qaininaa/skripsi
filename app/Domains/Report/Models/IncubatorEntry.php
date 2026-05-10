<?php

namespace App\Domains\Report\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain model for incubator medium in/out persistence.
 */
class IncubatorEntry extends Model
{
    use HasUuids;

    protected $table = 'incubator_entries';

    protected $fillable = [
        'incubator_id',
        'medium_type',
        'incubated_by',
        'date_in',
        'time_in',
        'removed_by',
        'date_out',
        'time_out',
    ];

    protected $casts = [
        'date_in' => 'date',
        'date_out' => 'date',
    ];

    public function incubator(): BelongsTo
    {
        return $this->belongsTo(Incubator::class);
    }

    public function incubatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incubated_by');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }
}
