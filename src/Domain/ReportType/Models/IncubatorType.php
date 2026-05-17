<?php

namespace Domain\ReportType\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Incubator configuration entry tied to a report type.
 */
class IncubatorType extends Model
{
    use HasUuids;

    protected $table = 'incubator_types';

    protected $fillable = ['report_type_id', 'temperature_label', 'min_day'];

    protected $casts = [
        'min_day' => 'integer',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
