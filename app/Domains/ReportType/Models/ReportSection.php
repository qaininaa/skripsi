<?php

namespace App\Domains\ReportType\Models;

use App\Domains\Location\Models\Location;
use App\Models\ReportSection as BaseReportSection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSection extends BaseReportSection
{
    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'section_id')
            ->orderBy('section_assigned_at')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
