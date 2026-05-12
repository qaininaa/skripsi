<?php

namespace App\Domains\Report\Models;

use App\Domains\ReportType\Models\PersonnelSamplingPoint;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelSamplingEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'personnel_row_id',
        'sampling_point_id',
        'cfu_bacteria',
        'cfu_fungi',
        'cfu_total',
        'kesimpulan',
    ];

    public function row(): BelongsTo
    {
        return $this->belongsTo(PersonnelRow::class, 'personnel_row_id');
    }

    public function samplingPoint(): BelongsTo
    {
        return $this->belongsTo(PersonnelSamplingPoint::class, 'sampling_point_id');
    }
}
