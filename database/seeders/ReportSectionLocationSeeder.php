<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportSectionLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder ini mengisi locations.section_id langsung.
        // Jalankan SETELAH: RoomSeeder, LocationSeeder, ReportTypeSectionSeeder
        //
        // Cara kerja: untuk setiap lokasi, cari section dari report_type yang cocok
        // berdasarkan measurement_type, lalu assign ke lokasi.

        // Ambil semua section beserta report_type code-nya
        $sections = DB::table('sections')
            ->join('report_types', 'sections.report_type_id', '=', 'report_types.id')
            ->select('sections.id', 'sections.measurement_type', 'report_types.annex_number as rt_code')
            ->get();

        // Kelompokkan: sectionByTypeAndAnnex[18]['settle_plate'] = section_id
        $sectionMap = [];
        foreach ($sections as $s) {
            $sectionMap[$s->rt_code][$s->measurement_type] = $s->id;
        }

        // Ambil semua lokasi
        $locations = DB::table('locations')
            ->join('rooms', 'locations.room_id', '=', 'rooms.id')
            ->select('locations.id', 'locations.measurement_type', 'rooms.room_name')
            ->get();

        // Map berdasarkan annex_number integer (18, 24, 3, 4)
        $roomToAnnex = [
            // Annex 18 — Filling Line 2
            'LAF Mesin Filling 2' => 18,
            'Filling Room 2' => 18,
            'Material Airlock In 1' => 18,
            'Material Airlock Out 1' => 18,
            'Equipment Store 2' => 18,
            'Personnel Airlock In 1' => 18,
            'Personnel Airlock Out 1' => 18,
            'Change Room In 1' => 18,
            'Change Room Out 1' => 18,

            // Annex 24 — Sampling Room
            'LAF Sampling Room' => 24,
            'Sampling Room' => 24,
            'Material Airlock In 3' => 24,
            'Material Airlock Out 3' => 24,
            'Change Room 5' => 24,
            'Personnel Airlock 5' => 24,

            // Annex 3
            'LAF Washing Machine (Laundry)' => 3,
            'LAF Getinge (Cleaned Parts Storage)' => 3,
            'Material Airlock 4' => 3,
            'Personnel Airlock 4' => 3,
            'Grade C Corridor' => 3,
            'Clean Preparation Room' => 3,
            'Equipment Store 1' => 3,
            'Formulation 1' => 3,
            'Formulation 2' => 3,
            'Equipment Clean and Dry' => 3,
            'Material Airlock 5' => 3,
            'Locker' => 3,
            'Change Room 3' => 3,

            // Annex 4
            'Change Room 1' => 4,
            'Personnel Airlock 3' => 4,
            'Weighed Material Store' => 4,
            'Weighing Room' => 4,
            'LAF Weighing Room' => 4,
            'Material Airlock 3' => 4,
            'Change Room 2' => 4,
            'Pre Weighing Staging' => 4,
            'Material Airlock 2' => 4,
            'Grade D Corridor 1' => 4,
            'Laundry' => 4,
            'Part Washing (D)' => 4,
            'Ampoule/Vial Stores 2' => 4,
            'Washing & Depyrogenation Line 2' => 4,
            'Equipment Dirty 2' => 4,
            'Equipment Store 5' => 4,
            'Janitor 3' => 4,
            'Equipment Dirty' => 4,
            'COP Washer' => 4,
            'Ampoule Stores 1' => 4,
            'Washing & Depyrogenation Line 1' => 4,
            'Change Room 4' => 4,
            'Filled Ampoules Unload 1' => 4,
            'Filled Ampoules Unload 2' => 4,
            'Material Airlock 6' => 4,
            'Grade D Corridor 2' => 4,
        ];

        $assigned = 0;
        foreach ($locations as $loc) {
            $annexCode = $roomToAnnex[$loc->room_name] ?? null;
            if (! $annexCode) {
                continue;
            }

            $sectionId = $sectionMap[$annexCode][$loc->measurement_type] ?? null;
            if (! $sectionId) {
                continue;
            }

            $affected = DB::table('locations')
                ->where('id', $loc->id)
                ->whereNull('section_id')
                ->update(['section_id' => $sectionId]);

            $assigned += $affected;
        }

        if ($assigned > 0) {
            $this->command->info('Assigned '.$assigned.' location(s) to sections.');
        } else {
            $this->command->warn('No locations assigned. Pastikan LocationSeeder dan ReportTypeSectionSeeder sudah dijalankan.');
        }
    }
}
