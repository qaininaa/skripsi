<?php

namespace App\Domains\AnalystReport\ReportSubmission\Services;

use App\Domains\AnalystReport\Models\AnalystReport;
use App\Domains\AnalystReport\ReportDrafting\DTOs\ReportDraftingSaveDTO;
use App\Domains\AnalystReport\ReportSubmission\Repositories\ReportSubmissionRepository;
use App\Services\Reports\ReportEntryService;
use App\Services\Reports\ReportWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Service for non-draft analyst actions (monitoring/read transitions and submit).
 */
class ReportSubmissionService
{
    public function __construct(
        private ReportSubmissionRepository $repository,
        private ReportEntryService $entryService,
        private ReportWorkflowService $workflowService,
    ) {}

    /**
     * Save report payload then apply non-draft workflow action.
     *
     * @param AnalystReport $report
     * @param ReportDraftingSaveDTO $dto
     * @return RedirectResponse
     */
    public function saveAndApplyAction(AnalystReport $report, ReportDraftingSaveDTO $dto): RedirectResponse
    {
        abort_if(in_array($report->status, ['submitted', 'approved'], true), 403);
        abort_if((string) $report->locked_by !== (string) Auth::id(), 403);

        if ($dto->action === 'save') {
            return back()->with('error', 'Aksi ini khusus simpan draft.');
        }

        $invalidFields = $this->entryService->validateCfu($dto->entries);
        if (! empty($invalidFields)) {
            return back()
                ->withInput()
                ->withErrors(['cfu' => 'Terdapat ' . count($invalidFields) . ' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC. Nilai nol, desimal, dan negatif tidak diperbolehkan.']);
        }

        $request = request();
        [$savedSectionIds, $hasPersonnelData] = $this->entryService->process($request, $report);

        $this->workflowService->recordParticipation($report, (string) Auth::id());
        $this->workflowService->stampSignatures($report, $savedSectionIds, $dto->action);

        if ($hasPersonnelData) {
            $this->workflowService->stampPersonnelSignature($report, $dto->action);
        }

        if ($dto->action === 'submit') {
            abort_unless($report->status === 'reading', 403);
            abort_if(empty($dto->supervisorId), 422, 'Pilih supervisor terlebih dahulu.');
            abort_unless($this->repository->supervisorExists((string) $dto->supervisorId), 422, 'Supervisor tidak valid.');

            $this->workflowService->submit($report, (string) $dto->supervisorId);

            return redirect()->route('laporan.index')
                ->with('success', 'Laporan berhasil dikirim ke supervisor.');
        }

        if ($dto->action === 'finish_monitoring') {
            abort_unless($report->status === 'monitoring', 403);
            $this->workflowService->finishMonitoring($report);

            return redirect()->route('laporan.index')
                ->with('success', 'Monitoring selesai. Laporan masuk ke tahap pembacaan.');
        }

        if ($dto->action === 'submit_revision') {
            abort_unless($report->status === 'monitoring', 403);
            $this->workflowService->submitRevision($report);

            return redirect()->route('laporan.index')
                ->with('success', 'Revisi berhasil dikirim ke supervisor.');
        }

        if ($dto->action === 'handover') {
            $this->workflowService->handover($report);

            return redirect()->route('laporan.index')
                ->with('success', 'Draft tersimpan. Laporan bisa dilanjutkan oleh analis lain.');
        }

        return back()->with('error', 'Aksi tidak dikenali.');
    }

    /**
     * Verify current user credentials before sensitive submission actions.
     *
     * @param string $username
     * @param string $password
     * @return array{ok: bool, message?: string}
     */
    public function verifyCredentials(string $username, string $password): array
    {
        $user = Auth::user();

        if ($user->username !== $username || ! Hash::check($password, $user->password)) {
            return ['ok' => false, 'message' => 'Username atau password salah.'];
        }

        return ['ok' => true];
    }
}
