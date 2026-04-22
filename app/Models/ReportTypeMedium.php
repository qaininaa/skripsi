<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportTypeMedium extends Model
{
    use HasUuids;

    protected $table = 'report_type_mediums';

    protected $fillable = ['report_type_id', 'name'];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
