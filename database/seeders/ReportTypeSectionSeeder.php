<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportTypeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ---------------------------------------------------------------
        // Annex 18: HVAC 6.1.1 B Filling Line 2
        // ---------------------------------------------------------------
        $annex18 = DB::table('report_types')->insertGetId([
            'code'         => 'HVAC-6.1.1-B-FL2',
            'name'         => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 B Filling Line 2',
            'annex_number' => 'Annex 18',
            'instrument'   => 'air_sampler',
            'medium_groups' => json_encode([
                'medium_tsp_60' => 'Medium TSP 60mm',
                'medium_tsp_90' => 'Medium TSP 90mm',
                'medium_swab'   => 'Swab Kit',
            ]),
            'incubators' => json_encode([
                '20_25' => ['label' => '20-25°C', 'min_days' => 5],
                '30_35' => ['label' => '30-35°C', 'min_days' => 3],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert([
            [
                'report_type_id'   => $annex18,
                'name'             => 'Settle Plate',
                'slug'             => 'settle_plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_exposure'     => 4,
                'order'            => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex18,
                'name'             => 'Air Sampler',
                'slug'             => 'air_sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_exposure'     => 2,
                'order'            => 2,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex18,
                'name'             => 'Contact Plate',
                'slug'             => 'contact_plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_exposure'     => 1,
                'order'            => 3,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex18,
                'name'             => 'Swab',
                'slug'             => 'swab',
                'measurement_unit' => 'CFU/25cm2/plate',
                'measurement_type' => 'swab',
                'max_exposure'     => 2,
                'order'            => 4,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Annex 24: HVAC 6.1.5
        // ---------------------------------------------------------------
        $annex24 = DB::table('report_types')->insertGetId([
            'code'         => 'HVAC-6.1.5',
            'name'         => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.5',
            'annex_number' => 'Annex 24',
            'instrument'   => 'air_sampler',
            'medium_groups' => json_encode([
                'medium_tsp_65' => 'Medium TSP 65mm',
                'medium_tsp_90' => 'Medium TSP 90mm',
            ]),
            'incubators' => json_encode([
                '20_25' => ['label' => '20-25°C', 'min_days' => 5],
                '30_35' => ['label' => '30-35°C', 'min_days' => 3],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert([
            [
                'report_type_id'   => $annex24,
                'name'             => 'Settle Plate',
                'slug'             => 'settle_plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_exposure'     => 3,
                'order'            => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex24,
                'name'             => 'Air Sampler',
                'slug'             => 'air_sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_exposure'     => 2,
                'order'            => 2,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex24,
                'name'             => 'Contact Plate',
                'slug'             => 'contact_plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_exposure'     => 2,
                'order'            => 3,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Annex 3
        // ---------------------------------------------------------------
        $annex3 = DB::table('report_types')->insertGetId([
            'code'         => 'HVAC-ANNEX3',
            'name'         => 'Laporan Pemantauan Ruangan Produksi HVAC Annex 3',
            'annex_number' => 'Annex 3',
            'instrument'   => 'air_sampler',
            'medium_groups' => json_encode([
                'medium_tsp_60' => 'Medium TSP 60mm',
                'medium_tsp_90' => 'Medium TSP 90mm',
            ]),
            'incubators' => json_encode([
                '20_25' => ['label' => '20-25°C', 'min_days' => 5],
                '30_35' => ['label' => '30-35°C', 'min_days' => 3],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert([
            [
                'report_type_id'   => $annex3,
                'name'             => 'Settle Plate',
                'slug'             => 'settle_plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_exposure'     => 1,
                'order'            => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex3,
                'name'             => 'Air Sampler',
                'slug'             => 'air_sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_exposure'     => 1,
                'order'            => 2,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex3,
                'name'             => 'Contact Plate',
                'slug'             => 'contact_plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_exposure'     => 1,
                'order'            => 3,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Annex 4
        // ---------------------------------------------------------------
        $annex4 = DB::table('report_types')->insertGetId([
            'code'         => 'HVAC-ANNEX4',
            'name'         => 'Laporan Pemantauan Ruangan Produksi HVAC Annex 4',
            'annex_number' => 'Annex 4',
            'instrument'   => 'air_sampler',
            'medium_groups' => json_encode([
                'medium_tsp_60' => 'Medium TSP 60mm',
                'medium_tsp_90' => 'Medium TSP 90mm',
            ]),
            'incubators' => json_encode([
                '20_25' => ['label' => '20-25°C', 'min_days' => 5],
                '30_35' => ['label' => '30-35°C', 'min_days' => 3],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sections')->insert([
            [
                'report_type_id'   => $annex4,
                'name'             => 'Settle Plate',
                'slug'             => 'settle_plate',
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'settle_plate',
                'max_exposure'     => 1,
                'order'            => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex4,
                'name'             => 'Air Sampler',
                'slug'             => 'air_sampler',
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'air_sampler',
                'max_exposure'     => 1,
                'order'            => 2,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'report_type_id'   => $annex4,
                'name'             => 'Contact Plate',
                'slug'             => 'contact_plate',
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'contact_plate',
                'max_exposure'     => 1,
                'order'            => 3,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Populate config columns based on measurement_type
        // ---------------------------------------------------------------
        $configMap = [
            'settle_plate'  => ['column_label' => 'Exposure', 'time_slot_type' => 'dual_ab',  'has_shared_time' => true,  'has_shift_toggle' => true],
            'air_sampler'   => ['column_label' => 'Shift',    'time_slot_type' => 'single',   'has_shared_time' => false, 'has_shift_toggle' => true],
            'contact_plate' => ['column_label' => 'Shift',    'time_slot_type' => 'none',     'has_shared_time' => false, 'has_shift_toggle' => true],
            'swab'          => ['column_label' => 'Shift',    'time_slot_type' => 'swab',     'has_shared_time' => false, 'has_shift_toggle' => true],
        ];

        foreach ($configMap as $type => $cfg) {
            DB::table('sections')
                ->where('measurement_type', $type)
                ->update($cfg);
        }
    }
}
