<?php

namespace Domain\Report\Models;

use Domain\ReportType\Models\IncubatorType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain model for incubator persistence in report entry flow.
 */
class Incubator extends Model
{
    use HasUuids;

    protected $table = 'incubators';

    protected $fillable = [
        'report_id',
        'report_type_incubator_id',
        'no_id',
        'calibration_date',
        'due_date_calibration',
    ];

    protected $casts = [
        'calibration_date' => 'date',
        'due_date_calibration' => 'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function incubatorType(): BelongsTo
    {
        return $this->belongsTo(IncubatorType::class, 'report_type_incubator_id');
    }

    public function reportTypeIncubator(): BelongsTo
    {
        return $this->incubatorType();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IncubatorEntry::class);
    }
}
