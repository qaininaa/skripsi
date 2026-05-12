<?php

namespace App\Domains\Report\Models;

use App\Domains\ReportType\Models\PersonnelMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelInstance extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'personnel_section_method_id',
        'page_number',
        'note',
        'deviation',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PersonnelMethod::class, 'personnel_section_method_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(PersonnelRow::class)->orderBy('row_order');
    }
}
