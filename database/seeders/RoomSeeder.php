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

            ['room_name' => 'LAF Mesin Filling 1',     'room_number' => '061P069', 'class' => 'A', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filling Room 1',          'room_number' => '061P069', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock In 2',   'room_number' => '061C063', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock Out 2',  'room_number' => '061C065', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 3',       'room_number' => '061P070', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock In 2',  'room_number' => '061C071', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock Out 2', 'room_number' => '061C068', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room In 2',        'room_number' => '061C072', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room Out 2',       'room_number' => '061C067', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],

            ['room_name' => 'LAF Sterility Room',      'room_number' => '061P075', 'class' => 'A', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'LAF Mesin Filling 2',     'room_number' => '061P075', 'class' => 'A', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filling Room 2',          'room_number' => '061P075', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock In 1',   'room_number' => '061C062', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock Out 1',  'room_number' => '061C064', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 2',       'room_number' => '061P076', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock In 1',  'room_number' => '061C077', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock Out 1', 'room_number' => '061C074', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock In 3',  'room_number' => '062C040', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock Out 3', 'room_number' => '062C041', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room In 1',        'room_number' => '061C078', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room Out 1',       'room_number' => '061C073', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Sterility Room',          'room_number' => '061C073', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room Out 3',       'room_number' => '062C039', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room In 4',        'room_number' => '062C057', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Store room 3',            'room_number' => '062Q043', 'class' => 'B', 'created_at' => $now, 'updated_at' => $now],

            ['room_name' => 'LAF Washing Machine (Laundry)',         'room_number' => '',         'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'LAF Getinge (Cleaned Parts Storage)',   'room_number' => '',         'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 4',                    'room_number' => '061C048',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock 4',                   'room_number' => '061C050',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Grade C Corridor',                      'room_number' => '',         'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Clean Preparation Room',                'room_number' => '',         'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 1',                     'room_number' => '061P053',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Formulation 1',                         'room_number' => '061P055',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Formulation 2',                         'room_number' => '061P054',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Clean and Dry',               'room_number' => '061P056',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 5',                    'room_number' => '061C057',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Locker',                                'room_number' => '061C066',  'class' => 'C', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 3',                         'room_number' => '061C049',  'class' => 'D', 'created_at' => $now, 'updated_at' => $now],

            ['room_name' => 'Change Room 1',                     'room_number' => '061C029', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock 3',               'room_number' => '061C030', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Weighed Material Store',            'room_number' => '061G042', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Weighing Room',                     'room_number' => '061P040', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'LAF Weighing Room',                 'room_number' => '',        'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 3',                'room_number' => '061C041', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 2',                     'room_number' => '061C039', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Pre Weighing Staging',              'room_number' => '061G038', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 2',                'room_number' => '061C031', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Grade D Corridor 1',                'room_number' => '',        'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Laundry',                           'room_number' => '061P034', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Part Washing (D)',                  'room_number' => '061C035', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Ampoule/Vial Stores 2',             'room_number' => '061G036', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Washing & Depyrogenation Line 2',   'room_number' => '061P037', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Dirty 2',                 'room_number' => '061G043', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Store 5',                 'room_number' => '061G044', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Janitor 3',                         'room_number' => '061U045', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Equipment Dirty',                   'room_number' => '061P047', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'COP Washer',                        'room_number' => '061P058', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Ampoule Stores 1',                  'room_number' => '061G046', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Washing & Depyrogenation Line 1',   'room_number' => '',        'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 4',                     'room_number' => '061C079', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filled Ampoules Unload 1',          'room_number' => '061P094', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Filled Ampoules Unload 2',          'room_number' => '061P093', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock 6',                'room_number' => '061C084', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Grade D Corridor 2',                'room_number' => '061C092', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],

            ['room_name' => 'Material Airlock In 3',      'room_number' => '061C112', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Material Airlock Out 3',     'room_number' => '061C113', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Change Room 5',              'room_number' => '061C114', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Personnel Airlock 5',        'room_number' => '061C115', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'Sampling Room',              'room_number' => '061P116', 'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
            ['room_name' => 'LAF Sampling Room',          'room_number' => '',        'class' => 'D', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('rooms')->insert(
            array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], $rooms)
        );
    }
}
