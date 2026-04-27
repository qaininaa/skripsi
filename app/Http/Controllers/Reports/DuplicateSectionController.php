<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reports\Sections\SectionInstanceService;

/**
 * DuplicateSectionController
 *
 * Tanggung jawab: handle HTTP request untuk duplikasi section.
 *
 * Diakses oleh:
 *   - Analis  → saat mengerjakan laporan
 *   - SPV     → saat preview/review laporan
 *   - Manager → saat preview/review laporan
 *
 * Logic duplikasi ada di SectionInstanceService, bukan di controller.
 *
 * Lokasi: app/Http/Controllers/Section/DuplicateSectionController.php
 */
class DuplicateSectionController extends Controller
{
    public function __construct(
        private SectionInstanceService $sectionInstanceService
    ) {}

    /**
     * POST /reports/{report}/sections/{sectionId}/duplicate
     *
     * Duplikat satu section.
     */
    public function duplicate(Report $report, string $sectionId)
    {
        // Otorisasi: laporan harus aktif dan user punya akses
        $this->authorizeAccess($report);

        $result = $this->sectionInstanceService->duplicate(
            $report,
            $sectionId,
        );

        return $this->respond($result);
    }

    /**
     * DELETE /reports/{report}/sections/{sectionId}
     *
     * Hapus satu duplikasi section (minimum tersisa 1).
     */
    public function remove(Report $report, string $sectionId)
    {
        $this->authorizeAccess($report);

        $result = $this->sectionInstanceService->remove($report, $sectionId);

        return $this->respond($result);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────

    /**
     * Cek otorisasi akses duplikasi.
     * Laporan harus dalam status yang masih bisa diedit.
     * Tidak cek locked_by karena SPV & Manager tidak punya lock tapi tetap bisa duplikat.
     */
    private function authorizeAccess(Report $report): void
    {
        $editableStatuses = ['monitoring', 'reading', 'submitted', 'returned', 'returned_to_supervisor'];
        abort_unless(in_array($report->status, $editableStatuses), 403);
    }

    /**
     * Return JSON atau redirect tergantung jenis request.
     */
    private function respond(array $result): mixed
    {
        $status = $result['ok'] ? 200 : 422;

        if (request()->wantsJson()) {
            return response()->json($result, $status);
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}
