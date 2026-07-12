<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan RoomSeeder dan FrequencySeeder sudah dijalankan dulu
        // Jalankan: php artisan db:seed --class=FrequencySeeder
        //           php artisan db:seed --class=RoomSeeder
        //           php artisan db:seed --class=LocationSeeder
        //
        // Kolom tabel locations:
        //   id, id_room (FK rooms), frequency_id (FK frequencies),
        //   location_number, measurement_type,
        //   alert_limit_total, alert_limit_fungi,
        //   alert_action_total, alert_action_fungi

        $now = now();

        // Ambil id room dan frequency sebagai referensi
        $laf1   = DB::table('rooms')->where('room_name', 'LAF Mesin Filling 2')->value('id');
        $fill2  = DB::table('rooms')->where('room_name', 'Filling Room 2')->value('id');

        $harian = DB::table('frequencies')->where('name', 'Harian')->value('id');

        DB::table('locations')->insert([
            // LAF Mesin Filling 2 — Settle Plate SP1
            [
                'id_room'               => $laf1,
                'frequency_id'          => $harian,
                'location_number'       => 'SP1',
                'measurement_type'      => 'settle_plate',
                'alert_limit_total'  => null,
                'alert_limit_fungi'     => null,
                'alert_action_total' => 1,
                'alert_action_fungi'    => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            // LAF Mesin Filling 2 — Air Sampler AS1
            [
                'id_room'               => $laf1,
                'frequency_id'          => $harian,
                'location_number'       => 'AS1',
                'measurement_type'      => 'air_sampler',
                'alert_limit_total'  => null,
                'alert_limit_fungi'     => null,
                'alert_action_total' => 1,
                'alert_action_fungi'    => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            // Filling Room 2 — Settle Plate SP1
            [
                'id_room'               => $fill2,
                'frequency_id'          => $harian,
                'location_number'       => 'SP1',
                'measurement_type'      => 'settle_plate',
                'alert_limit_total'  => 2,
                'alert_limit_fungi'     => null,
                'alert_action_total' => 5,
                'alert_action_fungi'    => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
        ]);
    }
}
