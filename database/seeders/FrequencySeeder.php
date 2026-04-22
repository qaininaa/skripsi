<?php

namespace Database\Seeders;

use App\Models\Frequency;
use Illuminate\Database\Seeder;

class FrequencySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['operational', 'daily', 'weekly', 'monthly', 'semi_annual'] as $name) {
            Frequency::firstOrCreate(['name' => $name]);
        }
    }
}
