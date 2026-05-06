<?php

namespace App\Services;

use App\Models\PersonnelInstance;
use App\Models\PersonnelRow;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

/**
 * PersonnelInstanceService
 *
 * Mengelola halaman monitoring personel pada laporan.
 * Setiap "halaman" terdiri dari satu PersonnelInstance per PersonnelMethod,
 * dengan page_number yang sama.
 *
 * Struktur baris per halaman:
 *   - Halaman 1 : 2 baris/method (ringkas)
 *   - Halaman 2+ : 4 baris/method (detail)
 */
class PersonnelInstanceService
{
    private const MAX_PAGES     = 5;
    private const ROWS_PAGE_1   = 2;
    private const ROWS_PAGE_2UP = 4;

    /**
     * Tambah satu halaman personel baru.
     *
     * @return array{ok: bool, message: string}
     */
    public function addPage(Report $report): array
    {
        $report->loadMissing('reportType.personnelMethods');

        $methods = $report->reportType->personnelMethods ?? collect();
        if ($methods->isEmpty()) {
            return ['ok' => false, 'message' => 'Tidak ada metode personel untuk laporan ini.'];
        }

        // Pastikan baseline halaman 1 selalu ada.
        $this->ensurePage1Initialized($report);
        $report->refresh();

        $maxPage = $report->personnelInstances()->max('page_number') ?? 1;
        // UI selalu menampilkan minimal 2 halaman (halaman 2 bisa fallback dari halaman 1),
        // jadi tombol tambah harus menambah dari halaman yang user lihat.
        $effectiveMaxPage = max(2, (int) $maxPage);

        if ($effectiveMaxPage >= self::MAX_PAGES) {
            return ['ok' => false, 'message' => 'Maksimum ' . self::MAX_PAGES . ' halaman personel.'];
        }

        $newPage  = $effectiveMaxPage + 1;
        $rowCount = $newPage === 1 ? self::ROWS_PAGE_1 : self::ROWS_PAGE_2UP;

        DB::transaction(function () use ($report, $methods, $newPage, $rowCount) {
            foreach ($methods as $method) {
                $instance = PersonnelInstance::firstOrCreate([
                    'report_id'                   => $report->id,
                    'personnel_section_method_id' => $method->id,
                    'page_number'                 => $newPage,
                ]);

                for ($i = 1; $i <= $rowCount; $i++) {
                    PersonnelRow::firstOrCreate([
                        'personnel_instance_id' => $instance->id,
                        'row_order'             => $i,
                    ]);
                }
            }
        });

        return ['ok' => true, 'message' => 'Halaman personel ' . $newPage . ' berhasil ditambahkan.'];
    }

    /**
     * Hapus satu halaman personel.
     * Halaman 1 tidak boleh dihapus.
     *
     * @return array{ok: bool, message: string}
     */
    public function removePage(Report $report, int $pageNum): array
    {
        if ($pageNum <= 1) {
            return ['ok' => false, 'message' => 'Halaman pertama tidak dapat dihapus.'];
        }

        $deleted = $report->personnelInstances()
            ->where('page_number', $pageNum)
            ->delete();

        if (! $deleted) {
            return ['ok' => false, 'message' => 'Halaman tidak ditemukan.'];
        }

        return ['ok' => true, 'message' => 'Halaman personel berhasil dihapus.'];
    }

    /**
     * Pastikan instance halaman 1 sudah ada untuk semua method.
     * Dipanggil saat laporan pertama kali dibuat.
     */
    public function ensurePage1Initialized(Report $report): void
    {
        $report->loadMissing('reportType.personnelMethods');

        $methods = $report->reportType->personnelMethods ?? collect();
        if ($methods->isEmpty()) {
            return;
        }

        $existingMethodIds = $report->personnelInstances()
            ->where('page_number', 1)
            ->pluck('personnel_section_method_id')
            ->all();

        DB::transaction(function () use ($report, $methods, $existingMethodIds) {
            foreach ($methods as $method) {
                if (in_array((string) $method->id, array_map('strval', $existingMethodIds), true)) {
                    continue;
                }

                $instance = PersonnelInstance::create([
                    'report_id'                   => $report->id,
                    'personnel_section_method_id' => $method->id,
                    'page_number'                 => 1,
                ]);

                for ($i = 1; $i <= self::ROWS_PAGE_1; $i++) {
                    PersonnelRow::create([
                        'personnel_instance_id' => $instance->id,
                        'row_order'             => $i,
                    ]);
                }
            }
        });
    }
}