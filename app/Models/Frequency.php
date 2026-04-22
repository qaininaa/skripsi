<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Frequency extends Model
{
    use HasUuids;

    protected $fillable = ['name'];

    public function locations()
    {
        return $this->hasMany(ReportLocation::class, 'frequency_id');
    }

    public function getIndonesianLabel(): string
    {
        return match ($this->name) {
            'operational' => 'Operasional',
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'semi_annual' => '6 Bulan',
            default => ucfirst($this->name),
        };
    }
}
