<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportTypeIncubator extends Model
{
    use HasUuids;

    protected $table = 'report_type_incubators';

    protected $fillable = ['report_type_id', 'temperature_label', 'min_days'];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
