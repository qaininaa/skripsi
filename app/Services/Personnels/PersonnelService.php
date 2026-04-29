<?php

namespace App\Services\Personnels;

use App\Models\PersonnelMethod;
use App\Models\ReportType;

class PersonnelService
{
    /**
     * Struktur default sesuai SOP-QC020-A04
     * Di-hardcode karena tidak berubah-ubah
     */
    private array $defaultStructure = [
        [
            'method'          => 'Cawan Kontak',
            'activities'      => [
                'Akhir proses analisa',
                'Keluar ruang filling',
            ],
            'sampling_points' => [
                'Dahi',
                'Dada Tengah',
                'Pergelangan Tangan Kanan',
                'Pergelangan Tangan Kiri',
            ],
            'limits' => [
                ['class' => 'b', 'limit_type' => 'alert',  'cfu_total' => 3,  'cfu_fungi' => 1],
                ['class' => 'b', 'limit_type' => 'action', 'cfu_total' => 5,  'cfu_fungi' => 1],
                ['class' => 'c', 'limit_type' => 'alert',  'cfu_total' => 10, 'cfu_fungi' => 2],
                ['class' => 'c', 'limit_type' => 'action', 'cfu_total' => 25, 'cfu_fungi' => 5],
            ],
        ],
        [
            'method'          => 'Finger Dab',
            'activities'      => [
                'Intervensi',
                'Akhir proses analisa',
                'Selesai set up',
                'Keluar ruang filling',
            ],
            'sampling_points' => [
                'Tangan Kanan',
                'Tangan Kiri',
            ],
            'limits' => [
                ['class' => 'b', 'limit_type' => 'alert',  'cfu_total' => 3,  'cfu_fungi' => 1],
                ['class' => 'b', 'limit_type' => 'action', 'cfu_total' => 5,  'cfu_fungi' => 1],
                ['class' => 'c', 'limit_type' => 'alert',  'cfu_total' => 10, 'cfu_fungi' => 2],
                ['class' => 'c', 'limit_type' => 'action', 'cfu_total' => 25, 'cfu_fungi' => 5],
            ],
        ],
    ];

    public function generate(ReportType $reportType): void
    {
        // Hapus yang lama dulu kalau ada
        $reportType->personnelMethods()->delete();

        foreach ($this->defaultStructure as $data) {
            $method = $reportType->personnelMethods()->create([
                'method' => $data['method'],
            ]);

            foreach ($data['activities'] as $activity) {
                $method->activities()->create(['activity' => $activity]);
            }

            foreach ($data['sampling_points'] as $point) {
                $method->samplingPoints()->create(['sampling_point' => $point]);
            }

            foreach ($data['limits'] as $limit) {
                $method->limits()->create($limit);
            }
        }
    }

    public function remove(ReportType $reportType): void
    {
        $reportType->personnelMethods()->delete();
    }
}