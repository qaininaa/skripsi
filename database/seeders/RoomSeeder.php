<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rooms = [

            // Annex 2
            ['room_name' => 'LAF Mesin Filling 2',    'room_number' => '061P075', 'class' => 'A', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filling Room 2',          'room_number' => '061P075', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock In 1',   'room_number' => '061C062', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock Out 1',  'room_number' => '061C064', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 2',       'room_number' => '061P076', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock In 1',  'room_number' => '061C077', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock Out 1', 'room_number' => '061C074', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room In 1',        'room_number' => '061C078', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room Out 1',       'room_number' => '061C073', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],

            // Rooms dari Annex 24 (HVAC 6.1.5)
            ['room_name' => 'LAF Sampling Room',     'room_number' => '061P116', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Sampling Room',         'room_number' => '061P116', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock In 3', 'room_number' => '061C112', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock Out 3', 'room_number' => '061C113', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 5',         'room_number' => '061C114', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock 5',   'room_number' => '061C115', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],

            // Annex 3
            ['room_name' => 'LAF Washing Machine (Laundry)',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'LAF Getinge (Cleaned Parts Storage)',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 4',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock 4',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Grade C Corridor',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Clean Preparation Room',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 1',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Formulation 1',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Formulation 2',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Clean and Dry',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 5',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Locker',   'room_number' => '', 'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 3',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],

            // Annex 4
            ['room_name' => 'Change Room 1',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock 3',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Weighed Material Store',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Weighing Room',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'LAF Weighing Room',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 3',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Pre Weighing Staging',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Grade D Corridor 1',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Laundry',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Part Washing (D)',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Ampoule/Vial Stores 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Washing & Depyrogenation Line 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Dirty 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 5',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Janitor 3',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Dirty',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'COP Washer',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Ampoule Stores 1',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Washing & Depyrogenation Line 1',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 4',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filled Ampoules Unload 1',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filled Ampoules Unload 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 6',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Grade D Corridor 2',   'room_number' => '', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],

        ];

        DB::table('rooms')->insert(
            array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], $rooms)
        );
    }
}
