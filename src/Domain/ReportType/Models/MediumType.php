<?php

namespace Domain\ReportType\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Medium type entry tied to a report type.
 */
class MediumType extends Model
{
    use HasUuids;

    protected $table = 'medium_types';

    protected $fillable = ['report_type_id', 'name'];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
