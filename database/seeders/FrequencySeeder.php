<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrequencySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('frequencies')->insert([
            ['name' => 'Operasional',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Harian',       'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mingguan',     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bulanan',      'created_at' => $now, 'updated_at' => $now],
            ['name' => '6 Bulan',      'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
