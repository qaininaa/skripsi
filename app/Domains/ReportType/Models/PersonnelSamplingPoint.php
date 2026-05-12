<?php

namespace App\Domains\ReportType\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelSamplingPoint extends Model
{
    use HasUuids;

    protected $fillable = ['personnel_section_method_id', 'sampling_point'];

    public function method(): BelongsTo
    {
        return $this->belongsTo(PersonnelMethod::class, 'personnel_section_method_id');
    }
}
