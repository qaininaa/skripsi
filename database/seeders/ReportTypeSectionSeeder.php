<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportTypeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ---------------------------------------------------------------
        // Annex 18: HVAC 6.1.1 B Filling Line 2
        // ---------------------------------------------------------------
        $annex18 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id'            => $annex18,
            'name'          => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 B Filling Line 2',
            'annex_number'  => 18,
            'sop_code'     => 'SOP-QC035-A18',
            'sop_version'  => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert(array_map(fn($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex18,
                'name' => 'Settle Plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_column' => 4,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex18,
                'name' => 'Air Sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_column' => 2,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex18,
                'name' => 'Contact Plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_column' => 1,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex18,
                'name' => 'Swab',
                'measurement_unit' => 'CFU/25cm2/plate',
                'measurement_type' => 'swab',
                'max_column' => 2,
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 24: HVAC 6.1.5
        // ---------------------------------------------------------------
        $annex24 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id'            => $annex24,
            'name'          => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.5',
            'annex_number'  => 24,
            'sop_code'     => 'SOP-QC035-A24',
            'sop_version'  => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert(array_map(fn($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex24,
                'name' => 'Settle Plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_column' => 3,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex24,
                'name' => 'Air Sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_column' => 2,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex24,
                'name' => 'Contact Plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_column' => 2,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 3
        // ---------------------------------------------------------------
        $annex3 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id'            => $annex3,
            'name'          => 'Laporan Pemantauan Ruangan Produksi HVAC Annex 3',
            'annex_number'  => 3,
            'sop_code'     => 'SOP-QC035-A3',
            'sop_version'  => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert(array_map(fn($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex3,
                'name' => 'Settle Plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_column' => 1,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex3,
                'name' => 'Air Sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_column' => 1,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex3,
                'name' => 'Contact Plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_column' => 1,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 4
        // ---------------------------------------------------------------
        $annex4 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id'            => $annex4,
            'name'          => 'Laporan Pemantauan Ruangan Produksi HVAC Annex 4',
            'annex_number'  => 4,
            'sop_code'     => 'SOP-QC035-A4',
            'sop_version'  => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert(array_map(fn($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex4,
                'name' => 'Settle Plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_column' => 1,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex4,
                'name' => 'Air Sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_column' => 1,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex4,
                'name' => 'Contact Plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_column' => 1,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Populate config columns based on measurement_type
        // ---------------------------------------------------------------
        $configMap = [
            'settle_plate'  => ['column_label' => 'Exposure', 'time_slot_type' => 'dual_ab', 'has_machine_setup' => true],
            'air_sampler'   => ['column_label' => 'Shift',    'time_slot_type' => 'single',  'has_machine_setup' => false],
            'contact_plate' => ['column_label' => 'Shift',    'time_slot_type' => 'none',    'has_machine_setup' => false],
            'swab'          => ['column_label' => 'Shift',    'time_slot_type' => 'swab',    'has_machine_setup' => false],
        ];

        foreach ($configMap as $type => $cfg) {
            DB::table('sections')
                ->where('measurement_type', $type)
                ->update($cfg);
        }
    }
}
