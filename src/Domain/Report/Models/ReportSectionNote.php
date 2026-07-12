<?php

namespace Domain\Report\Models;

use Domain\ReportType\Models\ReportSection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportSectionNote extends Model
{
    use HasUuids;

    protected $table = 'report_section_notes';

    protected $fillable = [
        'report_id',
        'section_id',
        'instance_number',
        'notes',
        'conclusion',
    ];

    protected function casts(): array
    {
        return [
            'instance_number' => 'integer',
        ];
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function section()
    {
        return $this->belongsTo(ReportSection::class, 'section_id');
    }
}
