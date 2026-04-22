<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PasswordSettingSeeder::class,
            AccountSeeder::class,
            FrequencySeeder::class,
            RoomSeeder::class,
            LocationSeeder::class,
            ReportTypeSectionSeeder::class,
            ReportSectionLocationSeeder::class,
        ]);
    }
}
