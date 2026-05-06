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

        // Room IDs
        $laf1       = DB::table('rooms')->where('room_name', 'LAF Mesin Filling 1')->value('id');
        $fill1      = DB::table('rooms')->where('room_name', 'Filling Room 1')->value('id');
        $matIn2     = DB::table('rooms')->where('room_name', 'Material Airlock In 2')->value('id');
        $matOut2    = DB::table('rooms')->where('room_name', 'Material Airlock Out 2')->value('id');
        $persIn2    = DB::table('rooms')->where('room_name', 'Personnel Airlock In 2')->value('id');
        $persOut2   = DB::table('rooms')->where('room_name', 'Personnel Airlock Out 2')->value('id');
        $chgIn2     = DB::table('rooms')->where('room_name', 'Change Room In 2')->value('id');
        $chgOut2    = DB::table('rooms')->where('room_name', 'Change Room Out 2')->value('id');
        $eqStore3   = DB::table('rooms')->where('room_name', 'Equipment Store 3')->value('id');

        $laf2      = DB::table('rooms')->where('room_name', 'LAF Mesin Filling 2')->value('id');
        $fill2     = DB::table('rooms')->where('room_name', 'Filling Room 2')->value('id');
        $matIn1    = DB::table('rooms')->where('room_name', 'Material Airlock In 1')->value('id');
        $matOut1   = DB::table('rooms')->where('room_name', 'Material Airlock Out 1')->value('id');
        $eqStore2  = DB::table('rooms')->where('room_name', 'Equipment Store 2')->value('id');
        $persIn1   = DB::table('rooms')->where('room_name', 'Personnel Airlock In 1')->value('id');
        $persOut1  = DB::table('rooms')->where('room_name', 'Personnel Airlock Out 1')->value('id');
        $chgIn1    = DB::table('rooms')->where('room_name', 'Change Room In 1')->value('id');
        $chgOut1   = DB::table('rooms')->where('room_name', 'Change Room Out 1')->value('id');

        $lafSteri  = DB::table('rooms')->where('room_name', 'LAF Sterility Room')->value('id');
        $steri     = DB::table('rooms')->where('room_name', 'Sterility Room')->value('id');
        $chgOut3   = DB::table('rooms')->where('room_name', 'Change Room Out 3')->value('id');
        $persIn3   = DB::table('rooms')->where('room_name', 'Personnel Airlock In 3')->value('id');
        $persOut3  = DB::table('rooms')->where('room_name', 'Personnel Airlock Out 3')->value('id');
        $store3    = DB::table('rooms')->where('room_name', 'Store Room 3')->value('id');
        $chgIn4    = DB::table('rooms')->where('room_name', 'Change Room In 4')->value('id');

        $locations = [

            // LAF Mesin Filling 1 — Class A — AL: NA/NA, ACL: <1/<1
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'SP2', 'measurement_type' => 'settle_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'S1',   'measurement_type' => 'swab', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'S1-2', 'measurement_type' => 'swab', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
            ['room_id' => $laf1,    'frequency' => 'daily', 'location_number' => 'S1-3', 'measurement_type' => 'swab', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
            
            // Filling Room 1 — Class B — AL T:2/F:NA, ACL T:5/F:<1
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'SP2', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'SP3', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'SP4', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'AS2', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill1,   'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Material Airlock In 2 — Class B
            ['room_id' => $matIn2,  'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matIn2,  'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $matIn2,  'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matIn2,  'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matIn2,  'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Material Airlock Out 2 — Class B
            ['room_id' => $matOut2, 'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matOut2, 'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $matOut2, 'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matOut2, 'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matOut2, 'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            
            // Personnel Airlock In 2 — Class B
            ['room_id' => $persIn2, 'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn2, 'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $persIn2, 'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn2, 'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn2, 'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Personnel Airlock Out 2 — Class B
            ['room_id' => $persOut2,'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut2,'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $persOut2,'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut2,'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut2,'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Change Room In 2 — Class B
            ['room_id' => $chgIn2,  'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn2,  'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn2,  'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn2,  'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn2,  'frequency' => 'daily', 'location_number' => 'CP3',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn2,  'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Change Room Out 2 — Class B
            ['room_id' => $chgOut2, 'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut2, 'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut2, 'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut2, 'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut2, 'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Equipment Store 3 — Class B
            ['room_id' => $eqStore3,'frequency' => 'daily', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore3,'frequency' => 'daily', 'location_number' => 'AS1', 'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore3,'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore3,'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore3,'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],




            // LAF Mesin Filling 2 — Class A — AL: NA/NA, ACL T:<1 F:<1
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1,  'alert_action_fungi' => 1],
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'SP2',  'measurement_type' => 'settle_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1,  'alert_action_fungi' => 1],
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1,  'alert_action_fungi' => 1],
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'S1',   'measurement_type' => 'swab', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'S1-2', 'measurement_type' => 'swab', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
            ['room_id' => $laf2,    'frequency' => 'daily', 'location_number' => 'S1-3', 'measurement_type' => 'swab', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
 
            // Filling Room 2 — Class B — AL T:2/F:NA, ACL T:5/F:<1
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'SP2',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'SP3',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'SP4',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'AS2',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'CP3',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $fill2,   'frequency' => 'daily', 'location_number' => 'SCP2', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Material Airlock In 1 — Class B
            ['room_id' => $matIn1,  'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $matIn1,  'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $matIn1,  'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matIn1,  'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matIn1,  'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Material Airlock Out 1 — Class B
            ['room_id' => $matOut1, 'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $matOut1, 'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $matOut1, 'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matOut1, 'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $matOut1, 'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],

            // Equipment Store 2 — Class B
            ['room_id' => $eqStore2,'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $eqStore2,'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore2,'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore2,'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $eqStore2,'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Personnel Airlock In 1 — Class B
            ['room_id' => $persIn1, 'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $persIn1, 'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $persIn1, 'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn1, 'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn1, 'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Personnel Airlock Out 1 — Class B
            ['room_id' => $persOut1,'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $persOut1,'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $persOut1,'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut1,'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut1,'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Change Room In 1 — Class B
            ['room_id' => $chgIn1,  'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $chgIn1,  'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn1,  'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn1,  'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn1,  'frequency' => 'daily', 'location_number' => 'CP3',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn1,  'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Change Room Out 1 — Class B
            ['room_id' => $chgOut1, 'frequency' => 'daily', 'location_number' => 'SP1',  'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5,  'alert_action_fungi' => 1],
            ['room_id' => $chgOut1, 'frequency' => 'daily', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5,    'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut1, 'frequency' => 'daily', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut1, 'frequency' => 'daily', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut1, 'frequency' => 'daily', 'location_number' => 'SCP1', 'measurement_type' => 'contact_plate', 'alert_limit_total' => 3,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 


            // LAF Sterility Room — Class A — operational — AL: NA/NA, ACL T:<1 F:<1
            ['room_id' => $lafSteri,'frequency' => 'operational', 'location_number' => 'SP1', 'measurement_type' => 'settle_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $lafSteri,'frequency' => 'operational', 'location_number' => 'SP2', 'measurement_type' => 'settle_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => 1],
            ['room_id' => $lafSteri,'frequency' => 'operational', 'location_number' => 'CP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
            ['room_id' => $lafSteri,'frequency' => 'operational', 'location_number' => 'CP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => null, 'alert_limit_fungi' => null, 'alert_action_total' => 1, 'alert_action_fungi' => null],
 
            // Sterility Room — Class B — daily — AL T:2/F:NA, ACL T:5/F:<1
            ['room_id' => $steri,   'frequency' => 'daily', 'location_number' => 'SP1',   'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $steri,   'frequency' => 'daily',  'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5, 'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $steri,   'frequency' => 'daily', 'location_number' => 'CP1',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $steri,   'frequency' => 'daily', 'location_number' => 'CP2',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $steri,   'frequency' => 'daily', 'location_number' => 'CP3',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $steri,   'frequency' => 'daily', 'location_number' => 'SCP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $steri,   'frequency' => 'daily', 'location_number' => 'SCP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Change Room Out 3 — Class B — daily
            ['room_id' => $chgOut3, 'frequency' => 'daily', 'location_number' => 'SP1',   'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut3, 'frequency' => 'weekly', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5, 'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut3, 'frequency' => 'daily', 'location_number' => 'CP1',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut3, 'frequency' => 'daily', 'location_number' => 'CP2',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgOut3, 'frequency' => 'daily', 'location_number' => 'SCP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Personnel Airlock In 3 — Class B — daily
            ['room_id' => $persIn3, 'frequency' => 'daily', 'location_number' => 'SP1',   'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn3, 'frequency' => 'weekly', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5, 'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $persIn3, 'frequency' => 'daily', 'location_number' => 'CP1',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn3, 'frequency' => 'daily', 'location_number' => 'CP2',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persIn3, 'frequency' => 'daily', 'location_number' => 'SCP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Personnel Airlock Out 3 — Class B — daily
            ['room_id' => $persOut3,'frequency' => 'daily', 'location_number' => 'SP1',   'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut3,'frequency' => 'weekly', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5, 'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $persOut3,'frequency' => 'daily', 'location_number' => 'CP1',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut3,'frequency' => 'daily', 'location_number' => 'CP2',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $persOut3,'frequency' => 'daily', 'location_number' => 'SCP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Store Room 3 — Class B — daily
            ['room_id' => $store3,  'frequency' => 'daily', 'location_number' => 'SP1',   'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $store3,  'frequency' => 'daily',  'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5, 'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $store3,  'frequency' => 'daily', 'location_number' => 'CP1',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $store3,  'frequency' => 'daily', 'location_number' => 'CP2',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $store3,  'frequency' => 'daily', 'location_number' => 'SCP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
 
            // Change Room In 4 — Class B — daily
            ['room_id' => $chgIn4,  'frequency' => 'daily', 'location_number' => 'SP1',   'measurement_type' => 'settle_plate', 'alert_limit_total' => 2,    'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn4,  'frequency' => 'weekly', 'location_number' => 'AS1',  'measurement_type' => 'air_sampler', 'alert_limit_total' => 5, 'alert_limit_fungi' => null, 'alert_action_total' => 10, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn4,  'frequency' => 'daily', 'location_number' => 'CP1',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn4,  'frequency' => 'daily', 'location_number' => 'CP2',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn4,  'frequency' => 'daily', 'location_number' => 'CP3',   'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn4,  'frequency' => 'daily', 'location_number' => 'SCP1',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
            ['room_id' => $chgIn4,  'frequency' => 'daily', 'location_number' => 'SCP2',  'measurement_type' => 'contact_plate', 'alert_limit_total' => 3, 'alert_limit_fungi' => null, 'alert_action_total' => 5, 'alert_action_fungi' => 1],
        ];

        $rows = array_map(function ($r) use ($now) {
            return [
                'id'         => (string) Str::uuid(),
                'created_at' => $now,
                'updated_at' => $now,
                ...$r,
            ];
        }, $locations);

        DB::table('locations')->insert($rows);
    }
}