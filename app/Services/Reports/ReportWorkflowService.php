<?php

namespace App\Services\Reports;

use App\Models\Analyst;
use App\Models\Report;
use App\Models\ReportApproval;
use App\Models\ReportSignature;
use App\Models\PersonnelSignature;
use Illuminate\Support\Facades\Auth;

/**
 * ReportWorkflowService
 *
 * Menangani semua logika alur kerja (workflow) laporan:
 *   - Klaim / penguncian laporan ke analis
 *   - Pencatatan partisipasi analis
 *   - Stamping tanda tangan per seksi
 *   - Transisi status: finish_monitoring, submit, submit_revision, handover
 *   - Penandaan tanda tangan monitoring/reading saat submit
 */
class ReportWorkflowService
{
    /**
     * Ambil data approval yang menyebabkan laporan dikembalikan (status 'returned').
     * Mengembalikan null jika tidak ada, atau jika laporan tidak sedang berstatus 'returned'.
     */
    public function getReturnedApproval(Report $report): ?ReportApproval
    {
        return ReportApproval::where('report_id', $report->id)
            ->where('status', 'returned')
            ->with('user')
            ->first();
    }

    /**
     * Klaim laporan ke analis yang sedang login.
     *
     * Kondisi yang membolehkan klaim:
     *   - Status 'pending' atau 'returned' → set status = 'monitoring', locked_by = user ini
     *   - Status 'monitoring' dan locked_by null → set locked_by = user ini
     *   - Status 'reading' dan locked_by null → set locked_by = user ini + catat sebagai analis pembaca
     */
    public function claimReport(Report $report, string $userId): void
    {
        if (in_array($report->status, ['pending', 'returned'])
            || ($report->status === 'monitoring' && $report->locked_by === null)) {
            $report->update(['status' => 'monitoring', 'locked_by' => $userId]);

        } elseif ($report->status === 'reading' && $report->locked_by === null) {
            $report->update(['locked_by' => $userId]);

            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id'   => $userId,
                'type'      => 'reading',
            ]);
        }
    }

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
     * Stamp tanda tangan per seksi untuk seksi-seksi yang baru diisi datanya.
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
            ReportSignature::updateOrCreate(
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

    /**
     * Selesaikan tahap monitoring. Laporan masuk ke tahap 'reading'.
     * Kunci dilepas agar analis pembaca bisa mengambil laporan.
     */
    public function finishMonitoring(Report $report): void
    {
        Report::where('id', $report->id)->update(['status' => 'reading', 'locked_by' => null]);
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
        $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
        $report->update([
            'header_data' => $freshHd,
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

        $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
        $report->update([
            'header_data' => $freshHd,
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

    /**
     * Tandai tanda tangan monitoring dan reading sebagai selesai di header_data.
     * Dipanggil saat laporan disubmit untuk mencatat siapa yang menandatangani dan kapan.
     *
     * @param  array  $headerData  header_data saat ini
     * @param  Report $report      Laporan yang disubmit
     * @return array               header_data yang sudah diperbarui
     */
    public function markAnalystSignaturesAsSigned(array $headerData, Report $report): array
    {
        $monitoringUser = Analyst::where('report_id', $report->id)->where('type', 'monitoring')->first();
        $readingUser    = Analyst::where('report_id', $report->id)->where('type', 'reading')->first();

        $headerData['ttd_monitoring_id'] = $monitoringUser?->user_id;
        $headerData['ttd_dibaca_id']     = $readingUser?->user_id;

        $signedAt = now()->toDateTimeString();
        $headerData['ttd_monitoring_signed_at'] = $signedAt;
        $headerData['ttd_dibaca_signed_at']     = $signedAt;

        return $headerData;
    }

    /**
     * Stamp tanda tangan personel untuk analis yang benar-benar mengisi.
     * Aksi draft ('save') tidak menyimpan tanda tangan.
     */
    public function stampPersonnelSignature(Report $report, string $action): void
    {
        if ($action === 'save') {
            return; // draft tidak menyimpan tanda tangan personel
        }

        $role = $report->status === 'reading' ? 'reading' : 'monitoring';

        // Cek apakah user ini benar-benar mengisi personnel row
        $hasFilledPersonnel = \App\Models\PersonnelRow::whereHas('instance', fn ($q) =>
                $q->where('report_id', $report->id)
            )
            ->where('filled_by', Auth::id())
            ->whereNotNull('personnel_name')
            ->exists();

        if (! $hasFilledPersonnel) {
            return; // analis ini tidak ngisi personnel, skip
        }

        PersonnelSignature::updateOrCreate(
            [
                'report_id' => $report->id,
                'user_id'   => Auth::id(),
                'role'      => $role,
            ],
            ['signed_at' => now()]
        );
    }
}
