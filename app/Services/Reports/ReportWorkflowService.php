<?php

namespace App\Services\Reports;

use DateTimeInterface;
use Domain\Report\Models\Analyst;
use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Domain\Report\Models\SectionSignature;
use Illuminate\Support\Facades\Auth;

/**
 * ReportWorkflowService
 *
 * Menangani semua logika alur kerja (workflow) laporan:
 *   - Pencatatan partisipasi analis
 *   - Stamping tanda tangan per section
 *   - Transisi status: finish_monitoring, submit, submit_revision, handover
 */
class ReportWorkflowService
{
    /**
     * Catat partisipasi analis yang sedang login di tabel 'analysts'.
     * Dipanggil setiap kali analis menyimpan data laporan.
     * Idempoten — aman dipanggil berkali-kali.
     */
    public function recordParticipation(Report $report, string $userId): void
    {
        $analystType = $report->status === 'reading' ? 'reading' : 'monitoring';
        Analyst::updateOrCreate([
            'report_id' => $report->id,
            'user_id'   => $userId,
            'type'      => $analystType,
        ]);
    }

    /**
     * Stamp tanda tangan per section untuk section-section yang baru diisi datanya.
     * Hanya dijalankan jika aksi bukan 'save' (draft).
     *
     * @param  Report  $report          Laporan yang sedang diproses
     * @param  array   $savedSectionIds Array ['section_uuid|instance' => true]
     * @param  string  $action          Aksi saat ini (save/submit/finish_monitoring/dll)
     */
    public function stampSignatures(Report $report, array $savedSectionIds, string $action): void
    {
        if ($action === 'save' || empty($savedSectionIds)) {
            return;
        }

        $role = $report->status === 'reading' ? 'reading' : 'monitoring';
        foreach (array_keys($savedSectionIds) as $_sidInst) {
            [$_secId, $_instNum] = explode('|', $_sidInst, 2) + [1 => '1'];
            SectionSignature::updateOrCreate(
                [
                    'report_id'       => $report->id,
                    'section_id'      => $_secId,
                    'instance_number' => (int) $_instNum,
                    'user_id'         => Auth::id(),
                    'role'            => $role,
                ],
                ['signed_at' => now()]
            );
        }
    }

    public function stampSupervisorSignaturesForFilledSections(Report $report, string $userId, DateTimeInterface $signedAt): void
    {
        $analystSectionSignatures = SectionSignature::query()
            ->where('report_id', $report->id)
            ->whereIn('role', ['monitoring', 'reading'])
            ->whereNotNull('signed_at')
            ->get(['section_id', 'instance_number'])
            ->unique(fn (SectionSignature $signature) => $signature->section_id . '|' . (int) $signature->instance_number);

        SectionSignature::query()
            ->where('report_id', $report->id)
            ->where('user_id', $userId)
            ->where('role', 'supervisor')
            ->delete();

        foreach ($analystSectionSignatures as $signature) {
            SectionSignature::create([
                'report_id' => $report->id,
                'section_id' => $signature->section_id,
                'instance_number' => (int) $signature->instance_number,
                'user_id' => $userId,
                'role' => 'supervisor',
                'signed_at' => $signedAt,
            ]);
        }
    }

    /**
     * Selesaikan tahap monitoring. Laporan masuk ke tahap 'reading'.
     * Kunci dilepas agar analis pembaca bisa mengambil laporan.
     */
    public function finishMonitoring(Report $report): void
    {
        Report::where('id', $report->id)->update(['status' => 'reading', 'locked_by' => null]);
    }

    /**
     * Pindahkan laporan ke tahap reading tanpa melepas lock analis saat ini.
     * Dipakai pada flow revisi ketika user yang sama perlu lanjut mengisi pembacaan.
     */
    public function switchToReadingKeepingLock(Report $report, string $userId): void
    {
        Report::where('id', $report->id)->update([
            'status' => 'reading',
            'locked_by' => $userId,
        ]);
    }

    /**
     * Submit laporan ke supervisor untuk review.
     * Laporan harus berada di tahap 'reading'.
     *
     * @param  Report $report       Laporan yang akan disubmit
     * @param  string $supervisorId UUID user supervisor yang dipilih
     */
    public function submit(Report $report, string $supervisorId): void
    {
        $report->update([
            'status'      => 'submitted',
            'locked_by'   => null,
        ]);

        ReportApproval::updateOrCreate(
            ['report_id' => $report->id, 'step' => 2],
            [
                'role'                => 'Supervisor',
                'user_id'            => $supervisorId,
                'status'             => 'pending',
                'signed_at'          => null,
                'notes'              => null,
                'returned_to_user_id' => null,
            ]
        );
    }

    /**
     * Submit revisi laporan yang sebelumnya dikembalikan supervisor.
     * Approval step 2 yang sudah ada di-reset ke 'pending'.
     * Laporan harus pernah disubmit sebelumnya (step-2 approval harus ada).
     */
    public function submitRevision(Report $report): void
    {
        $existingStep2 = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->firstOrFail();

        $report->update([
            'status'      => 'submitted',
            'locked_by'   => null,
        ]);

        $existingStep2->update([
            'status'             => 'pending',
            'signed_at'          => null,
            'notes'              => null,
            'returned_to_user_id' => null,
        ]);
    }

    /**
     * Lepas kunci laporan agar analis lain bisa melanjutkan.
     * Data yang sudah tersimpan tetap ada.
     */
    public function handover(Report $report): void
    {
        Report::where('id', $report->id)->update(['locked_by' => null]);
    }
}
