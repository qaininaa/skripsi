<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActivity extends Model
{
    use HasUuids;

    protected $fillable = ['personnel_section_method_id', 'activity', 'order'];

    public function method(): BelongsTo
    {
        return $this->belongsTo(PersonnelMethod::class, 'personnel_section_method_id');
    }
}