<?php

namespace Database\Seeders;

use App\Domains\ReportType\Models\ReportType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportTypeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $mediaAnnex17 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
            ['name' => 'Swab Kit'],
        ];

        $mediaAnnex18 = [
            ['name' => 'Medium TSP 60mm'],
            ['name' => 'Medium TSP 90mm'],
            ['name' => 'Swab Kit'],
        ];

        
         $mediaAnnex21 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
            ['name' => 'Swab Kit'],
        ];

        $mediaAnnex22 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex23 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex24 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];
            
        $mediaAnnex34 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex35 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex36 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex37 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex38 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $mediaAnnex40 = [
            ['name' => 'Medium TSP 65mm'],
            ['name' => 'Medium TSP 90mm'],
        ];

        $incubatorsAnnex17 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex18 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex21 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex22 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex23 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex24 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex34 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex35 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex36 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex37 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex38 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        $incubatorsAnnex40 = [
            ['temperature_label' => '20–25°C', 'min_day' => 3],
            ['temperature_label' => '30–35°C', 'min_day' => 2],
        ];

        
        // ---------------------------------------------------------------
        // Annex 17: HVAC 6.1.1 A Filling Line 1
        // ---------------------------------------------------------------
        $annex17 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex17,
            'name' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 A Filling Line 1',
            'annex_number' => 17,
            'sop_code' => 'SOP-QC035-A17',
            'sop_version' => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex17, $mediaAnnex17, $now);
        $this->insertIncubators($annex17, $incubatorsAnnex17, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex17,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 4,
                'column_label' => 'Exposure',
                'time_slot_type' => 'dual_ab',
                'has_machine_setup' => true,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex17,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex17,
                'measurement_unit' => 'CFU/plate D=55mm/ ± 15 second/contact plate',
                'measurement_type' => 'Contact Plate',
                'max_column' => 1,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex17,
                'measurement_unit' => 'CFU/25cm2/plate',
                'measurement_type' => 'Swab',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'swab',
                'has_machine_setup' => false,
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 18: HVAC 6.1.1 B Filling Line 2
        // ---------------------------------------------------------------
        $annex18 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex18,
            'name' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 B Filling Line 2',
            'annex_number' => 18,
            'sop_code' => 'SOP-QC035-A18',
            'sop_version' => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex18, $mediaAnnex18, $now);
        $this->insertIncubators($annex18, $incubatorsAnnex18, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex18,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 4,
                'column_label' => 'Exposure',
                'time_slot_type' => 'dual_ab',
                'has_machine_setup' => true,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex18,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex18,
                'measurement_unit' => 'CFU/plate D=55mm/ ± 15 second/contact plate',
                'measurement_type' => 'Contact Plate',
                'max_column' => 1,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex18,
                'measurement_unit' => 'CFU/25cm2/plate',
                'measurement_type' => 'Swab',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'swab',
                'has_machine_setup' => false,
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 21: HVAC 6-2.1 
        // ---------------------------------------------------------------
        $annex21 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex21,
            'name' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6-2.1 (Pemantauan Harian dan Mingguan)',
            'annex_number' => 21,
            'sop_code' => 'SOP-QC035-A21',
            'sop_version' => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex21, $mediaAnnex21, $now);
        $this->insertIncubators($annex21, $incubatorsAnnex21, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex21,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex21,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex21,
                'measurement_unit' => 'CFU/plate D=55mm/ ± 15 second/contact plate',
                'measurement_type' => 'Contact Plate',
                'max_column' => 1,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex21,
                'measurement_unit' => 'CFU/25cm2/plate',
                'measurement_type' => 'Swab',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 22: HVAC 6-2.2
        // ---------------------------------------------------------------
        $annex22 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex22,
            'name' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6-2.2',
            'annex_number' => 22,
            'sop_code' => 'SOP-QC035-A22',
            'sop_version' => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex22, $mediaAnnex22, $now);
        $this->insertIncubators($annex22, $incubatorsAnnex22, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex22,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex22,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex22,
                'measurement_unit' => 'CFU/plate D=55mm/ ± 15 second/contact plate',
                'measurement_type' => 'Contact Plate',
                'max_column' => 1,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));
        
        // ---------------------------------------------------------------
        // Annex 23: HVAC 6-2.2
        // ---------------------------------------------------------------
        $annex23 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex23,
            'name' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6-2.3 (Pemantauan Bulanan dan Setiap Enam Bulan)',
            'annex_number' => 23,
            'sop_code' => 'SOP-QC035-A23',
            'sop_version' => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex23, $mediaAnnex23, $now);
        $this->insertIncubators($annex23, $incubatorsAnnex23, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex23,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex23,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex22,
                'measurement_unit' => 'CFU/plate D=55mm/ ± 15 second/contact plate',
                'measurement_type' => 'Contact Plate',
                'max_column' => 1,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));


        // ---------------------------------------------------------------
        // Annex 24: HVAC 6-1.5
        // ---------------------------------------------------------------
        $annex24 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex24,
            'name' => 'Laporan Pemantauan Ruangan HVAC 6-1.5',
            'annex_number' => 24,
            'sop_code' => 'SOP-QC035-A24',
            'sop_version' => '11',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex24, $mediaAnnex24, $now);
        $this->insertIncubators($annex24, $incubatorsAnnex24, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex24,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex24,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Exposure',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex24,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 34: HVAC 6-1.2
        // ---------------------------------------------------------------
        $annex34 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex34,
            'name' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6-1.2 (Pemantauan Mingguan)',
            'annex_number' => 34,
            'sop_code' => 'SOP-QC035-A34',
            'sop_version' => '06',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex34, $mediaAnnex34, $now);
        $this->insertIncubators($annex34, $incubatorsAnnex34, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex34,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex34,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex34,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 35: HVAC 6-1.2
        // ---------------------------------------------------------------
        $annex35 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex35,
            'name' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6-1.2 (Pemantauan Bulanan)',
            'annex_number' => 35,
            'sop_code' => 'SOP-QC035-A35',
            'sop_version' => '07',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex35, $mediaAnnex35, $now);
        $this->insertIncubators($annex35, $incubatorsAnnex35, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex35,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex35,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex35,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 36: HVAC 6-1.3
        // ---------------------------------------------------------------
        $annex36 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex36,
            'name' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6-1.3 (Pemantauan Mingguan dan Bulanan)',
            'annex_number' => 36,
            'sop_code' => 'SOP-QC036-A36',
            'sop_version' => '07',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex36, $mediaAnnex36, $now);
        $this->insertIncubators($annex36, $incubatorsAnnex36, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex36,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex36,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex36,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 37: HVAC 6-1.3
        // ---------------------------------------------------------------
        $annex37 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex37,
            'name' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6-1.3 (Pemantauan Setiap Enam Bulan)',
            'annex_number' => 37,
            'sop_code' => 'SOP-QC037-A37',
            'sop_version' => '06',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex37, $mediaAnnex37, $now);
        $this->insertIncubators($annex37, $incubatorsAnnex37, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex37,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex37,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex37,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 38: HVAC 6-2.3 
        // ---------------------------------------------------------------
        $annex38 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex38,
            'name' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6-2.3 (Pemantauan Mingguan)',
            'annex_number' => 38,
            'sop_code' => 'SOP-QC038-A38',
            'sop_version' => '05',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex38, $mediaAnnex38, $now);
        $this->insertIncubators($annex38, $incubatorsAnnex38, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex38,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex38,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex38,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));

        // ---------------------------------------------------------------
        // Annex 40: HVAC 6-2.1
        // ---------------------------------------------------------------
        $annex40 = (string) Str::uuid();
        DB::table('report_types')->insert([
            'id' => $annex40,
            'name' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6-2.1 (Pemantauan Bulanan dan 6 Bulanan)',
            'annex_number' => 40,
            'sop_code' => 'SOP-QC040-A40',
            'sop_version' => '01',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insertMedia($annex40, $mediaAnnex40, $now);
        $this->insertIncubators($annex40, $incubatorsAnnex40, $now);

        DB::table('sections')->insert(array_map(fn ($r) => ['id' => (string) Str::uuid(), ...$r], [
            [
                'report_type_id' => $annex40,
                'measurement_unit' => 'CFU/4hours/plate',
                'measurement_type' => 'Settle Plate',
                'max_column' => 3,
                'column_label' => 'Exposure',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex40,
                'measurement_unit' => 'CFU/10min/m3',
                'measurement_type' => 'Air Sampler',
                'max_column' => 2,
                'column_label' => 'Pemantauan',
                'time_slot_type' => 'per_location',
                'has_machine_setup' => false,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'report_type_id' => $annex40,
                'measurement_unit' => 'CFU/plate D=55mm/15sec',
                'measurement_type' => 'Contact Plate',
                'max_column' => 2,
                'column_label' => '',
                'time_slot_type' => 'single',
                'has_machine_setup' => false,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]));
    }

    private function insertMedia(string $reportTypeId, array $mediums, $now): void
    {
        foreach ($mediums as $m) {
            DB::table('medium_types')->insert([
                'id' => (string) Str::uuid(),
                'report_type_id' => $reportTypeId,
                'name' => $m['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function insertIncubators(string $reportTypeId, array $incubators, $now): void
    {
        foreach ($incubators as $inc) {
            DB::table('incubator_types')->insert([
                'id' => (string) Str::uuid(),
                'report_type_id' => $reportTypeId,
                'temperature_label' => $inc['temperature_label'],
                'min_day' => $inc['min_day'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
