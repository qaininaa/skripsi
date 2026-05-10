<?php

namespace App\Domains\Report\Models;

use App\Models\Report;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Domain model for instrument identity persistence in report entry flow.
 */
class InstrumentIdentityEntry extends Model
{
	use HasUuids;

	protected $table = 'instrument_entries';

	protected $fillable = [
		'report_id',
		'tool_name',
		'no_id',
		'calibration_date',
		'due_date',
	];

	protected $casts = [
		'calibration_date' => 'date',
		'due_date' => 'date',
	];

	public function report()
	{
		return $this->belongsTo(Report::class);
	}
}
