<?php

namespace App\Domains\ReportEntry\MediumEntry\Models;

use App\Models\Report;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Domain model for medium identity persistence in report entry flow.
 */
class MediumEntry extends Model
{
    use HasUuids;

    protected $table = 'medium_identities';

    protected $fillable = [
        'report_id',
        'medium_id',
        'name',
        'batch_number',
        'gpt_number',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
