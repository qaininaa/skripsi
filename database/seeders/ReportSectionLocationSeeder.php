<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSectionLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder ini mengisi tabel pivot report_section (id_section ↔ id_location).
        // Jalankan SETELAH: RoomSeeder, FrequencySeeder, LocationSeeder, ReportTypeSectionSeeder
        //
        // Cara kerja: untuk setiap lokasi, cari section dari report_type yang cocok
        // berdasarkan measurement_type, lalu hubungkan ke pivot.

        $now = now();

        // Ambil semua section beserta report_type code-nya
        $sections = DB::table('sections')
            ->join('report_types', 'sections.report_type_id', '=', 'report_types.id')
            ->select('sections.id', 'sections.measurement_type', 'report_types.code as rt_code')
            ->get();

        // Kelompokkan: sectionByTypeAndAnnex['HVAC-6.1.1-B-FL2']['settle_plate'] = section_id
        $sectionMap = [];
        foreach ($sections as $s) {
            $sectionMap[$s->rt_code][$s->measurement_type] = $s->id;
        }

        // Ambil semua lokasi
        $locations = DB::table('locations')
            ->join('rooms', 'locations.id_room', '=', 'rooms.id')
            ->select('locations.id', 'locations.measurement_type', 'rooms.room_name')
            ->get();

        // Tentukan lokasi mana yang masuk ke annex mana.
        // Aturan sederhana berdasarkan nama room → map ke report_type code.
        // Sesuaikan mapping ini jika ada annex lain.
        $roomToAnnex = [
            // Annex 18 — Filling Line 2
            'LAF Mesin Filling 2'    => 'HVAC-6.1.1-B-FL2',
            'Filling Room 2'         => 'HVAC-6.1.1-B-FL2',
            'Material Airlock In 1'  => 'HVAC-6.1.1-B-FL2',
            'Material Airlock Out 1' => 'HVAC-6.1.1-B-FL2',
            'Equipment Store 2'      => 'HVAC-6.1.1-B-FL2',
            'Personnel Airlock In 1' => 'HVAC-6.1.1-B-FL2',
            'Personnel Airlock Out 1'=> 'HVAC-6.1.1-B-FL2',
            'Change Room In 1'       => 'HVAC-6.1.1-B-FL2',
            'Change Room Out 1'      => 'HVAC-6.1.1-B-FL2',

            // Annex 24 — Sampling Room
            'LAF Sampling Room'      => 'HVAC-6.1.5',
            'Sampling Room'          => 'HVAC-6.1.5',
            'Material Airlock In 3'  => 'HVAC-6.1.5',
            'Material Airlock Out 3' => 'HVAC-6.1.5',
            'Change Room 5'          => 'HVAC-6.1.5',
            'Personnel Airlock 5'    => 'HVAC-6.1.5',

            // Annex 3
            'LAF Washing Machine (Laundry)'  => 'HVAC-ANNEX3',
            'LAF Getinge (Cleaned Parts Storage)' => 'HVAC-ANNEX3',
            'Material Airlock 4'     => 'HVAC-ANNEX3',
            'Personnel Airlock 4'    => 'HVAC-ANNEX3',
            'Grade C Corridor'       => 'HVAC-ANNEX3',
            'Clean Preparation Room' => 'HVAC-ANNEX3',
            'Equipment Store 1'      => 'HVAC-ANNEX3',
            'Formulation 1'          => 'HVAC-ANNEX3',
            'Formulation 2'          => 'HVAC-ANNEX3',
            'Equipment Clean and Dry'=> 'HVAC-ANNEX3',
            'Material Airlock 5'     => 'HVAC-ANNEX3',
            'Locker'                 => 'HVAC-ANNEX3',
            'Change Room 3'          => 'HVAC-ANNEX3',

            // Annex 4
            'Change Room 1'          => 'HVAC-ANNEX4',
            'Personnel Airlock 3'    => 'HVAC-ANNEX4',
            'Weighed Material Store' => 'HVAC-ANNEX4',
            'Weighing Room'          => 'HVAC-ANNEX4',
            'LAF Weighing Room'      => 'HVAC-ANNEX4',
            'Material Airlock 3'     => 'HVAC-ANNEX4',
            'Change Room 2'          => 'HVAC-ANNEX4',
            'Pre Weighing Staging'   => 'HVAC-ANNEX4',
            'Material Airlock 2'     => 'HVAC-ANNEX4',
            'Grade D Corridor 1'     => 'HVAC-ANNEX4',
            'Laundry'                => 'HVAC-ANNEX4',
            'Part Washing (D)'       => 'HVAC-ANNEX4',
            'Ampoule/Vial Stores 2'  => 'HVAC-ANNEX4',
            'Washing & Depyrogenation Line 2' => 'HVAC-ANNEX4',
            'Equipment Dirty 2'      => 'HVAC-ANNEX4',
            'Equipment Store 5'      => 'HVAC-ANNEX4',
            'Janitor 3'              => 'HVAC-ANNEX4',
            'Equipment Dirty'        => 'HVAC-ANNEX4',
            'COP Washer'             => 'HVAC-ANNEX4',
            'Ampoule Stores 1'       => 'HVAC-ANNEX4',
            'Washing & Depyrogenation Line 1' => 'HVAC-ANNEX4',
            'Change Room 4'          => 'HVAC-ANNEX4',
            'Filled Ampoules Unload 1' => 'HVAC-ANNEX4',
            'Filled Ampoules Unload 2' => 'HVAC-ANNEX4',
            'Material Airlock 6'     => 'HVAC-ANNEX4',
            'Grade D Corridor 2'     => 'HVAC-ANNEX4',
        ];

        $pivotRows = [];
        foreach ($locations as $loc) {
            $annexCode = $roomToAnnex[$loc->room_name] ?? null;
            if (! $annexCode) {
                continue;
            }

            $sectionId = $sectionMap[$annexCode][$loc->measurement_type] ?? null;
            if (! $sectionId) {
                continue;
            }

            $pivotRows[] = [
                'id_section'  => $sectionId,
                'id_location' => $loc->id,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        if (! empty($pivotRows)) {
            DB::table('report_section')->insertOrIgnore($pivotRows);
            $this->command->info('Inserted ' . count($pivotRows) . ' pivot row(s) into report_section.');
        } else {
            $this->command->warn('No pivot rows inserted. Pastikan LocationSeeder dan ReportTypeSectionSeeder sudah dijalankan.');
        }
    }
}
