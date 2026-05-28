<?php

namespace Domain\Report\Services;

use App\Services\Reports\ReportWorkflowService;
use Domain\Report\Dtos\ReportDraftingSaveDto;
use Domain\Report\Interfaces\ReportClaimingRepositoryInterface;
use Domain\Report\Interfaces\ReportSubmissionRepositoryInterface;
use Domain\Report\Models\AnalystReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Service for non-draft analyst actions (monitoring/read transitions and submit).
 */
class ReportSubmissionService
{
    public function __construct(
        private ReportSubmissionRepositoryInterface $repository,
        private ReportClaimingRepositoryInterface $claimingRepository,
        private ReportEntryService $entryService,
        private EnvironmentalEntryService $environmentalEntryService,
        private ReportWorkflowService $workflowService,
    ) {}

    /**
     * Save report payload then apply non-draft workflow action.
     */
    public function saveAndApplyAction(AnalystReport $report, ReportDraftingSaveDto $dto, Request $request): RedirectResponse
    {
        abort_if(in_array($report->status, ['submitted', 'approved'], true), 403);
        abort_if((string) $report->locked_by !== (string) Auth::id(), 403);

        if ($dto->action === 'save') {
            return back()->with('error', 'Aksi ini khusus simpan draft.');
        }

        $invalidFields = $this->environmentalEntryService->validateCfu($dto->entries);
        if (! empty($invalidFields)) {
            return back()
                ->withInput()
                ->with('focus_input', $invalidFields[0])
                ->withErrors(['cfu' => 'Terdapat ' . count($invalidFields) . ' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat 1 sampai 200, <1, atau TNTC. Nilai nol, desimal, negatif, dan angka di atas 200 tidak diperbolehkan.']);
        }

        if ($dto->action === 'finish_monitoring') {
            [$timeErrors] = $this->environmentalEntryService->validateMonitoringTimePairs($request);
            if (! empty($timeErrors)) {
                return back()
                    ->withInput()
                    ->withErrors($timeErrors);
            }
        }

        [$savedSectionIds] = $this->entryService->process($request, $report);

        $this->workflowService->recordParticipation($report, (string) Auth::id());
        $this->workflowService->stampSignatures($report, $savedSectionIds, $dto->action);

        if ($dto->action === 'submit') {
            abort_unless($report->status === 'reading', 403);
            abort_if(empty($dto->supervisorId), 422, 'Pilih supervisor terlebih dahulu.');
            abort_unless($this->repository->supervisorExists((string) $dto->supervisorId), 422, 'Supervisor tidak valid.');

            // Pastikan semua CFU sudah terisi sebelum kirim ke supervisor
            $submitReadiness = $this->claimingRepository->checkSubmitReadiness((string) $report->id);
            if (! $submitReadiness['ready']) {
                $missingList = implode('; ', array_slice($submitReadiness['missing'], 0, 5));
                $extraCount  = max(0, count($submitReadiness['missing']) - 5);
                $msg = 'Semua nilai CFU (B dan F) wajib diisi sebelum mengirim laporan ke supervisor. ' ;

                return back()
                    ->withInput()
                    ->withErrors(['cfu_incomplete' => $msg]);
            }

            $this->workflowService->submit($report, (string) $dto->supervisorId);

            return redirect()->route('reports.index')
                ->with('success', 'Laporan berhasil dikirim ke supervisor.');
        }

        if ($dto->action === 'finish_monitoring') {
            abort_unless($report->status === 'monitoring', 403);

            // Pastikan semua data wajib sudah terisi sebelum transisi ke tahap reading
            $readiness = $this->claimingRepository->checkReadingReadiness((string) $report->id);
            if (! $readiness['ready']) {
                $errors = [];
                if (in_array('Identitas Instrumen', $readiness['missing'], true)) {
                    $errors['instrument_incomplete'] = 'Semua field Identitas Instrumen wajib diisi sebelum melanjutkan ke tahap pembacaan.';
                }
                if (in_array('Identitas Medium', $readiness['missing'], true)) {
                    $errors['medium_incomplete'] = 'Semua field Identitas Medium wajib diisi sebelum melanjutkan ke tahap pembacaan.';
                }
                if (in_array('Proses Inkubasi Medium Monitoring', $readiness['missing'], true)) {
                    $errors['inkubator_incomplete'] = 'Data Proses Inkubasi Medium Monitoring wajib diisi sebelum melanjutkan ke tahap pembacaan.';
                }

                return back()
                    ->withInput()
                    ->withErrors($errors);
            }

            $this->workflowService->finishMonitoring($report);

            return redirect()->route('reports.index')
                ->with('success', 'Monitoring selesai. Laporan masuk ke tahap pembacaan.');
        }

        if ($dto->action === 'switch_to_reading') {
            abort_unless($report->status === 'monitoring', 403);
            $this->workflowService->switchToReadingKeepingLock($report, (string) Auth::id());

            return redirect()->route('reports.fill', $report)
                ->with('success', 'Anda dapat melanjutkan perbaikan pengisian pembacaan.');
        }

        if ($dto->action === 'submit_revision') {
            abort_unless(in_array($report->status, ['monitoring', 'reading'], true), 403);
            $this->workflowService->submitRevision($report);

            return redirect()->route('reports.index')
                ->with('success', 'Revisi berhasil dikirim ke supervisor.');
        }

        if ($dto->action === 'handover') {
            $this->workflowService->handover($report);

            return redirect()->route('reports.index')
                ->with('success', 'Draft tersimpan. Laporan bisa dilanjutkan oleh analis lain.');
        }

        return back()->with('error', 'Aksi tidak dikenali.');
    }

    /**
     * Verify current user credentials before sensitive submission actions.
     *
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
