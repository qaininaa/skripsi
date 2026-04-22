<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $laf1 = DB::table('rooms')->where('room_name', 'LAF Mesin Filling 2')->value('id');
        $fill2 = DB::table('rooms')->where('room_name', 'Filling Room 2')->value('id');

        $daily = DB::table('frequencies')->where('name', 'daily')->value('id');

        $locations = [
            [
                'room_id' => $laf1,
                'frequency_id' => $daily,
                'location_number' => 'SP1',
                'measurement_type' => 'settle_plate',
                'alert_limit_total' => null,
                'alert_limit_fungi' => null,
                'alert_action_total' => 1,
                'alert_action_fungi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'room_id' => $laf1,
                'frequency_id' => $daily,
                'location_number' => 'AS1',
                'measurement_type' => 'air_sampler',
                'alert_limit_total' => null,
                'alert_limit_fungi' => null,
                'alert_action_total' => 1,
                'alert_action_fungi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'room_id' => $fill2,
                'frequency_id' => $daily,
                'location_number' => 'SP1',
                'measurement_type' => 'settle_plate',
                'alert_limit_total' => 2,
                'alert_limit_fungi' => null,
                'alert_action_total' => 5,
                'alert_action_fungi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('locations')->insert(
            array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], $locations)
        );
    }
}
