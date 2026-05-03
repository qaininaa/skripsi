<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelMethod extends Model
{
    use HasUuids;

    protected $table = 'personnel_section_methods';

    protected $fillable = ['report_type_id', 'method'];

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PersonnelActivity::class, 'personnel_section_method_id')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function samplingPoints(): HasMany
    {
        return $this->hasMany(PersonnelSamplingPoint::class, 'personnel_section_method_id');
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PersonnelMethodLimit::class, 'personnel_section_method_id');
    }
}