<?php

namespace Database\Seeders;

use App\Models\ReportLocation;
use App\Models\ReportSection;
use App\Models\ReportType;
use Illuminate\Database\Seeder;

class ReportTypeSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------
        // Annex 18: Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 B Filling Line 2
        // ---------------------------------------------------------------
        $annex18 = ReportType::create([
            'code'         => 'HVAC-6.1.1-B-FL2',
            'name'         => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 B Filling Line 2',
            'annex_number' => 'Annex 18',
            'instrument'   => 'air_sampler',
            'frequency'    => 'harian',
            'medium_groups' => [
                'medium_tsp_60' => 'Medium TSP 60mm',
                'medium_tsp_90' => 'Medium TSP 90mm',
                'medium_swab'   => 'Swab Kit',
            ],
        ]);

        // Lokasi yang sama di semua seksi Annex 18
        $rooms = [
            ['s_no' => 1, 'room_name' => 'LAF Mesin Filling 2',   'class' => 'A', 'room_number' => '061P075'],
            ['s_no' => 2, 'room_name' => 'Filling Room 2',         'class' => 'B', 'room_number' => '061P075'],
            ['s_no' => 3, 'room_name' => 'Material Airlock In 1',  'class' => 'B', 'room_number' => '061C062'],
            ['s_no' => 4, 'room_name' => 'Material Airlock Out 1', 'class' => 'B', 'room_number' => '061C064'],
            ['s_no' => 5, 'room_name' => 'Equipment Store 2',      'class' => 'B', 'room_number' => '061P076'],
            ['s_no' => 6, 'room_name' => 'Personnel Airlock In 1', 'class' => 'B', 'room_number' => '061C077'],
            ['s_no' => 7, 'room_name' => 'Personnel Airlock Out 1','class' => 'B', 'room_number' => '061C074'],
            ['s_no' => 8, 'room_name' => 'Change Room In 1',       'class' => 'B', 'room_number' => '061C078'],
            ['s_no' => 9, 'room_name' => 'Change Room Out 1',      'class' => 'B', 'room_number' => '061C073'],
        ];

        // Seksi 1: Settle Plate (max 4 exposures per lokasi)
        $settleSection = ReportSection::create([
            'report_type_id'   => $annex18->id,
            'name'             => 'Settle Plate',
            'slug'             => 'settle_plate',
            'measurement_unit' => 'CFU/4hours/plate',
            'measurement_type' => 'settle_plate',
            'max_exposures'    => 4,
            'order'            => 1,
        ]);

        // Limits settle plate per kelas (Annex 18 dokumen)
        $settleLimits = [
            'A' => ['alert_b' => null, 'action_b' => 1,  'alert_f' => null, 'action_f' => 1],
            'B' => ['alert_b' => 2,    'action_b' => 5,  'alert_f' => null, 'action_f' => 1],
        ];

        // Lokasi & location_number untuk settle plate (ada beberapa SP per ruangan utk kelas B)
        $settleLocations = [
            ['s_no'=>1,'room_name'=>'LAF Mesin Filling 2',   'class'=>'A','room_number'=>'061P075','location_number'=>'SP1'],
            ['s_no'=>1,'room_name'=>'LAF Mesin Filling 2',   'class'=>'A','room_number'=>'061P075','location_number'=>'SP2'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'SP1'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'SP2'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'SP3'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'SP4'],
            ['s_no'=>3,'room_name'=>'Material Airlock In 1',  'class'=>'B','room_number'=>'061C062','location_number'=>'SP1'],
            ['s_no'=>4,'room_name'=>'Material Airlock Out 1', 'class'=>'B','room_number'=>'061C064','location_number'=>'SP1'],
            ['s_no'=>5,'room_name'=>'Equipment Store 2',      'class'=>'B','room_number'=>'061P076','location_number'=>'SP1'],
            ['s_no'=>6,'room_name'=>'Personnel Airlock In 1', 'class'=>'B','room_number'=>'061C077','location_number'=>'SP1'],
            ['s_no'=>7,'room_name'=>'Personnel Airlock Out 1','class'=>'B','room_number'=>'061C074','location_number'=>'SP1'],
            ['s_no'=>8,'room_name'=>'Change Room In 1',       'class'=>'B','room_number'=>'061C078','location_number'=>'SP1'],
            ['s_no'=>9,'room_name'=>'Change Room Out 1',      'class'=>'B','room_number'=>'061C073','location_number'=>'SP1'],
        ];

        foreach ($settleLocations as $loc) {
            $lim = $settleLimits[$loc['class']];
            ReportLocation::create([
                'report_section_id'      => $settleSection->id,
                'alert_limit_bacteria'   => $lim['alert_b'],
                'action_limit_bacteria'  => $lim['action_b'],
                'alert_limit_fungi'      => $lim['alert_f'],
                'action_limit_fungi'     => $lim['action_f'],
            ] + $loc);
        }

        // Seksi 2: Air Sampler (2 shift)
        $airSection = ReportSection::create([
            'report_type_id'   => $annex18->id,
            'name'             => 'Air Sampler',
            'slug'             => 'air_sampler',
            'measurement_unit' => 'CFU/10min/m3',
            'measurement_type' => 'air_sampler',
            'max_exposures'    => 2,
            'order'            => 2,
        ]);

        $airLimits = [
            'A' => ['alert_b' => null, 'action_b' => 1,  'alert_f' => null, 'action_f' => 1],
            'B' => ['alert_b' => 5,    'action_b' => 10, 'alert_f' => null, 'action_f' => 1],
        ];

        foreach ($rooms as $room) {
            $lim = $airLimits[$room['class']];
            // Semua ruangan punya setidaknya AS1
            ReportLocation::create([
                'report_section_id'      => $airSection->id,
                'location_number'        => 'AS1',
                'alert_limit_bacteria'   => $lim['alert_b'],
                'action_limit_bacteria'  => $lim['action_b'],
                'alert_limit_fungi'      => $lim['alert_f'],
                'action_limit_fungi'     => $lim['action_f'],
            ] + $room);
            // Filling Room 2 punya 2 titik air sampler
            if ($room['room_name'] === 'Filling Room 2') {
                ReportLocation::create([
                    'report_section_id'      => $airSection->id,
                    'location_number'        => 'AS2',
                    'alert_limit_bacteria'   => $lim['alert_b'],
                    'action_limit_bacteria'  => $lim['action_b'],
                    'alert_limit_fungi'      => $lim['alert_f'],
                    'action_limit_fungi'     => $lim['action_f'],
                ] + $room);
            }
        }

        // Seksi 3: Contact Plate (1 shift, banyak CP per ruangan)
        $contactSection = ReportSection::create([
            'report_type_id'   => $annex18->id,
            'name'             => 'Contact Plate',
            'slug'             => 'contact_plate',
            'measurement_unit' => 'CFU/plate D=55mm/15sec',
            'measurement_type' => 'contact_plate',
            'max_exposures'    => 1,
            'order'            => 3,
        ]);

        $contactLimits = [
            'A' => ['alert_b' => null, 'action_b' => 1, 'alert_f' => null, 'action_f' => 1],
            'B' => ['alert_b' => 3,    'action_b' => 5, 'alert_f' => null, 'action_f' => 1],
        ];

        $contactLocations = [
            ['s_no'=>1,'room_name'=>'LAF Mesin Filling 2',   'class'=>'A','room_number'=>'061P075','location_number'=>'CP1'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'CP1'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'CP2'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'CP3'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'SCP1'],
            ['s_no'=>2,'room_name'=>'Filling Room 2',         'class'=>'B','room_number'=>'061P075','location_number'=>'SCP2'],
            ['s_no'=>3,'room_name'=>'Material Airlock In 1',  'class'=>'B','room_number'=>'061C062','location_number'=>'CP1'],
            ['s_no'=>3,'room_name'=>'Material Airlock In 1',  'class'=>'B','room_number'=>'061C062','location_number'=>'CP2'],
            ['s_no'=>3,'room_name'=>'Material Airlock In 1',  'class'=>'B','room_number'=>'061C062','location_number'=>'SCP1'],
            ['s_no'=>4,'room_name'=>'Material Airlock Out 1', 'class'=>'B','room_number'=>'061C064','location_number'=>'CP1'],
            ['s_no'=>4,'room_name'=>'Material Airlock Out 1', 'class'=>'B','room_number'=>'061C064','location_number'=>'CP2'],
            ['s_no'=>4,'room_name'=>'Material Airlock Out 1', 'class'=>'B','room_number'=>'061C064','location_number'=>'SCP1'],
            ['s_no'=>5,'room_name'=>'Equipment Store 2',      'class'=>'B','room_number'=>'061P076','location_number'=>'CP1'],
            ['s_no'=>5,'room_name'=>'Equipment Store 2',      'class'=>'B','room_number'=>'061P076','location_number'=>'CP2'],
            ['s_no'=>5,'room_name'=>'Equipment Store 2',      'class'=>'B','room_number'=>'061P076','location_number'=>'SCP1'],
            ['s_no'=>6,'room_name'=>'Personnel Airlock In 1', 'class'=>'B','room_number'=>'061C077','location_number'=>'CP1'],
            ['s_no'=>6,'room_name'=>'Personnel Airlock In 1', 'class'=>'B','room_number'=>'061C077','location_number'=>'CP2'],
            ['s_no'=>6,'room_name'=>'Personnel Airlock In 1', 'class'=>'B','room_number'=>'061C077','location_number'=>'SCP1'],
            ['s_no'=>7,'room_name'=>'Personnel Airlock Out 1','class'=>'B','room_number'=>'061C074','location_number'=>'CP1'],
            ['s_no'=>7,'room_name'=>'Personnel Airlock Out 1','class'=>'B','room_number'=>'061C074','location_number'=>'CP2'],
            ['s_no'=>7,'room_name'=>'Personnel Airlock Out 1','class'=>'B','room_number'=>'061C074','location_number'=>'SCP1'],
            ['s_no'=>8,'room_name'=>'Change Room In 1',       'class'=>'B','room_number'=>'061C078','location_number'=>'CP1'],
            ['s_no'=>8,'room_name'=>'Change Room In 1',       'class'=>'B','room_number'=>'061C078','location_number'=>'CP2'],
            ['s_no'=>8,'room_name'=>'Change Room In 1',       'class'=>'B','room_number'=>'061C078','location_number'=>'CP3'],
            ['s_no'=>8,'room_name'=>'Change Room In 1',       'class'=>'B','room_number'=>'061C078','location_number'=>'SCP1'],
            ['s_no'=>9,'room_name'=>'Change Room Out 1',      'class'=>'B','room_number'=>'061C073','location_number'=>'CP1'],
            ['s_no'=>9,'room_name'=>'Change Room Out 1',      'class'=>'B','room_number'=>'061C073','location_number'=>'CP2'],
            ['s_no'=>9,'room_name'=>'Change Room Out 1',      'class'=>'B','room_number'=>'061C073','location_number'=>'SCP1'],
        ];

        foreach ($contactLocations as $loc) {
            $lim = $contactLimits[$loc['class']];
            ReportLocation::create([
                'report_section_id'      => $contactSection->id,
                'alert_limit_bacteria'   => $lim['alert_b'],
                'action_limit_bacteria'  => $lim['action_b'],
                'alert_limit_fungi'      => $lim['alert_f'],
                'action_limit_fungi'     => $lim['action_f'],
            ] + $loc);
        }

        // Seksi 4: Swab (2 shift, hanya LAF)
        $swabSection = ReportSection::create([
            'report_type_id'   => $annex18->id,
            'name'             => 'Swab',
            'slug'             => 'swab',
            'measurement_unit' => 'CFU/25cm2/plate',
            'measurement_type' => 'swab',
            'max_exposures'    => 2,
            'order'            => 4,
        ]);

        ReportLocation::create([
            'report_section_id'      => $swabSection->id,
            's_no'                   => 1,
            'room_name'              => 'LAF Mesin Filling 2',
            'class'                  => 'A',
            'room_number'            => '061P075',
            'location_number'        => 'S1',
            'alert_limit_bacteria'   => null,
            'action_limit_bacteria'  => 1,
            'alert_limit_fungi'      => null,
            'action_limit_fungi'     => null,
        ]);

        // ---------------------------------------------------------------
        // Annex 24: Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.5
        // ---------------------------------------------------------------
        $annex24 = ReportType::create([
            'code'         => 'HVAC-6.1.5',
            'name'         => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.5',
            'annex_number' => 'Annex 24',
            'instrument'   => 'air_sampler',
            'frequency'    => 'campuran',
            'medium_groups' => [
                'medium_tsp_65' => 'Medium TSP 65mm',
                'medium_tsp_90' => 'Medium TSP 90mm',
            ],
        ]);

        // Ruangan Annex 24
        $rooms24 = [
            ['s_no' => 1, 'room_name' => 'LAF Sampling Room',    'class' => 'C', 'room_number' => '061P116'],
            ['s_no' => 2, 'room_name' => 'Sampling Room',        'class' => 'D', 'room_number' => '061P116'],
            ['s_no' => 3, 'room_name' => 'Material Airlock In 3','class' => 'D', 'room_number' => '061C112'],
            ['s_no' => 4, 'room_name' => 'Material Airlock Out 3','class' => 'D', 'room_number' => '061C113'],
            ['s_no' => 5, 'room_name' => 'Change Room 5',        'class' => 'D', 'room_number' => '061C114'],
            ['s_no' => 6, 'room_name' => 'Personnel Airlock 5',  'class' => 'D', 'room_number' => '061C115'],
        ];

        // Seksi 1: Settle Plate (3 exposure per lokasi)
        $settleLimits24 = [
            'C' => ['alert_b' => 25,   'action_b' => 50,  'alert_f' => 2,    'action_f' => 5],
            'D' => ['alert_b' => null, 'action_b' => 50,  'alert_f' => null, 'action_f' => 10],
        ];

        $settleSection24 = ReportSection::create([
            'report_type_id'   => $annex24->id,
            'name'             => 'Settle Plate',
            'slug'             => 'settle_plate',
            'measurement_unit' => 'CFU/4hours/plate',
            'measurement_type' => 'settle_plate',
            'max_exposures'    => 3,
            'order'            => 1,
        ]);

        foreach ($rooms24 as $room) {
            $lim = $settleLimits24[$room['class']];
            ReportLocation::create([
                'report_section_id'     => $settleSection24->id,
                'location_number'       => 'SP1',
                'alert_limit_bacteria'  => $lim['alert_b'],
                'action_limit_bacteria' => $lim['action_b'],
                'alert_limit_fungi'     => $lim['alert_f'],
                'action_limit_fungi'    => $lim['action_f'],
            ] + $room);
        }

        // Seksi 2: Air Sampler (2 shift)
        $airLimits24 = [
            'C' => ['alert_b' => 25,  'action_b' => 100, 'alert_f' => 2,  'action_f' => 5],
            'D' => ['alert_b' => 50,  'action_b' => 200, 'alert_f' => 5,  'action_f' => 10],
        ];

        $airSection24 = ReportSection::create([
            'report_type_id'   => $annex24->id,
            'name'             => 'Air Sampler',
            'slug'             => 'air_sampler',
            'measurement_unit' => 'CFU/10min/m3',
            'measurement_type' => 'air_sampler',
            'max_exposures'    => 2,
            'order'            => 2,
        ]);

        foreach ($rooms24 as $room) {
            $lim = $airLimits24[$room['class']];
            ReportLocation::create([
                'report_section_id'     => $airSection24->id,
                'location_number'       => 'AS1',
                'alert_limit_bacteria'  => $lim['alert_b'],
                'action_limit_bacteria' => $lim['action_b'],
                'alert_limit_fungi'     => $lim['alert_f'],
                'action_limit_fungi'    => $lim['action_f'],
            ] + $room);
        }

        // Seksi 3: Contact Plate (2 shift)
        $contactLimits24 = [
            'C' => ['alert_b' => 10,  'action_b' => 25,  'alert_f' => 2,  'action_f' => 5],
            'D' => ['alert_b' => 20,  'action_b' => 50,  'alert_f' => 2,  'action_f' => 10],
        ];

        $contactSection24 = ReportSection::create([
            'report_type_id'   => $annex24->id,
            'name'             => 'Contact Plate',
            'slug'             => 'contact_plate',
            'measurement_unit' => 'CFU/plate D=55mm/15sec',
            'measurement_type' => 'contact_plate',
            'max_exposures'    => 2,
            'order'            => 3,
        ]);

        $contactLocations24 = [
            ['s_no'=>1,'room_name'=>'LAF Sampling Room',    'class'=>'C','room_number'=>'061P116','location_number'=>'CP1'],
            ['s_no'=>1,'room_name'=>'LAF Sampling Room',    'class'=>'C','room_number'=>'061P116','location_number'=>'CP2'],
            ['s_no'=>1,'room_name'=>'LAF Sampling Room',    'class'=>'C','room_number'=>'061P116','location_number'=>'CP3'],
            ['s_no'=>1,'room_name'=>'LAF Sampling Room',    'class'=>'C','room_number'=>'061P116','location_number'=>'CP4'],
            ['s_no'=>2,'room_name'=>'Sampling Room',        'class'=>'D','room_number'=>'061P116','location_number'=>'CP1'],
            ['s_no'=>2,'room_name'=>'Sampling Room',        'class'=>'D','room_number'=>'061P116','location_number'=>'CP2'],
            ['s_no'=>2,'room_name'=>'Sampling Room',        'class'=>'D','room_number'=>'061P116','location_number'=>'CP3'],
            ['s_no'=>3,'room_name'=>'Material Airlock In 3','class'=>'D','room_number'=>'061C112','location_number'=>'CP1'],
            ['s_no'=>3,'room_name'=>'Material Airlock In 3','class'=>'D','room_number'=>'061C112','location_number'=>'SCP1'],
            ['s_no'=>4,'room_name'=>'Material Airlock Out 3','class'=>'D','room_number'=>'061C113','location_number'=>'CP1'],
            ['s_no'=>4,'room_name'=>'Material Airlock Out 3','class'=>'D','room_number'=>'061C113','location_number'=>'SCP1'],
            ['s_no'=>5,'room_name'=>'Change Room 5',        'class'=>'D','room_number'=>'061C114','location_number'=>'CP1'],
            ['s_no'=>5,'room_name'=>'Change Room 5',        'class'=>'D','room_number'=>'061C114','location_number'=>'SCP1'],
            ['s_no'=>6,'room_name'=>'Personnel Airlock 5',  'class'=>'D','room_number'=>'061C115','location_number'=>'CP1'],
        ];

        foreach ($contactLocations24 as $loc) {
            $lim = $contactLimits24[$loc['class']];
            ReportLocation::create([
                'report_section_id'     => $contactSection24->id,
                'alert_limit_bacteria'  => $lim['alert_b'],
                'action_limit_bacteria' => $lim['action_b'],
                'alert_limit_fungi'     => $lim['alert_f'],
                'action_limit_fungi'    => $lim['action_f'],
            ] + $loc);
        }
    }
}
