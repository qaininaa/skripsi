<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSectionLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Manual mapping: isi satu-per-satu agar urutan section per annex bisa dikontrol.
        // section_order diambil dari kolom sections.order milik report_type (annex) terkait.
        $manualAssignments = [
            // Contoh format:
            // [
            //     'annex_number' => 17,
            //     'section_order' => 1,
            //     'room_name' => 'LAF Mesin Filling 1',
            //     'location_number' => 'SP1', // optional
            //     'measurement_type' => 'settle_plate', // optional
            // ],
        ];

        if ($manualAssignments === []) {
            $this->command?->warn('ReportSectionLocationSeeder: belum ada mapping manual. Tidak ada assignment yang dijalankan.');

            return;
        }

        $reportTypeIdCache = [];
        $sectionIdCache = [];
        $assigned = 0;

        foreach ($manualAssignments as $index => $assignment) {
            $annexNumber = (int) ($assignment['annex_number'] ?? 0);
            $sectionOrder = (int) ($assignment['section_order'] ?? 0);
            $roomName = (string) ($assignment['room_name'] ?? '');
            $locationNumber = isset($assignment['location_number']) ? (string) $assignment['location_number'] : null;
            $measurementType = isset($assignment['measurement_type']) ? $this->normalizeMeasurementType((string) $assignment['measurement_type']) : null;

            if ($annexNumber === 0 || $sectionOrder === 0 || $roomName === '') {
                $this->command?->warn('Baris mapping #'.($index + 1).' invalid (annex_number, section_order, room_name wajib diisi).');
                continue;
            }

            $reportTypeId = $this->resolveReportTypeIdByAnnex($annexNumber, $reportTypeIdCache);
            if (! $reportTypeId) {
                $this->command?->warn('Baris mapping #'.($index + 1).': report_type annex '.$annexNumber.' tidak ditemukan.');
                continue;
            }

            $sectionId = $this->resolveSectionIdByOrder($reportTypeId, $sectionOrder, $sectionIdCache);
            if (! $sectionId) {
                $this->command?->warn('Baris mapping #'.($index + 1).': section order '.$sectionOrder.' tidak ditemukan pada annex '.$annexNumber.'.');
                continue;
            }

            $roomIds = DB::table('rooms')
                ->where('room_name', $roomName)
                ->pluck('id');

            if ($roomIds->isEmpty()) {
                $this->command?->warn('Baris mapping #'.($index + 1).': room "'.$roomName.'" tidak ditemukan.');
                continue;
            }

            $query = DB::table('locations')
                ->whereIn('room_id', $roomIds)
                ->whereNull('section_id');

            if ($locationNumber !== null && $locationNumber !== '') {
                $query->where('location_number', $locationNumber);
            }

            if ($measurementType !== null && $measurementType !== '') {
                $query->where('measurement_type', $measurementType);
            }

            $affected = $query->update([
                'section_id' => $sectionId,
                'section_assigned_at' => now(),
            ]);

            $assigned += $affected;
        }

        if ($assigned > 0) {
            $this->command?->info('Assigned '.$assigned.' location(s) via manual mapping.');
        } else {
            $this->command?->warn('Tidak ada location yang ter-assign. Cek isi manual mapping pada seeder ini.');
        }
    }

    private function resolveReportTypeIdByAnnex(int $annexNumber, array &$cache): ?string
    {
        if (array_key_exists($annexNumber, $cache)) {
            return $cache[$annexNumber];
        }

        $cache[$annexNumber] = DB::table('report_types')
            ->where('annex_number', $annexNumber)
            ->value('id');

        return $cache[$annexNumber];
    }

    private function resolveSectionIdByOrder(string $reportTypeId, int $sectionOrder, array &$cache): ?string
    {
        $cacheKey = $reportTypeId.'|'.$sectionOrder;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $cache[$cacheKey] = DB::table('sections')
            ->where('report_type_id', $reportTypeId)
            ->where('order', $sectionOrder)
            ->value('id');

        return $cache[$cacheKey];
    }

    private function normalizeMeasurementType(string $value): string
    {
        $value = trim(strtolower($value));
        $value = str_replace(['/', '-', ' '], '_', $value);

        return preg_replace('/_+/', '_', $value) ?? $value;
    }
}
