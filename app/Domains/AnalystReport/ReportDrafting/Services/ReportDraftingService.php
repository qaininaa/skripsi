<?php

namespace App\Domains\AnalystReport\ReportDrafting\Services;

use App\Domains\AnalystReport\Models\AnalystReport;
use App\Domains\AnalystReport\ReportDrafting\DTOs\ReportDraftingSaveDTO;
use App\Services\PersonnelInstanceService;
use App\Services\Reports\ReportEntryService;
use App\Services\Reports\ReportWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Service for analyst report drafting flow (draft-only actions).
 */
class ReportDraftingService
{
    public function __construct(
        private PersonnelInstanceService $personnelInstanceService,
        private ReportEntryService $entryService,
        private ReportWorkflowService $workflowService,
    ) {}

    /**
     * Save report form payload as draft only.
     *
     * @param AnalystReport $report
     * @param ReportDraftingSaveDTO $dto
     * @return RedirectResponse
     */
    public function saveDraft(AnalystReport $report, ReportDraftingSaveDTO $dto): RedirectResponse
    {
        abort_if(in_array($report->status, ['submitted', 'approved'], true), 403);
        abort_if((string) $report->locked_by !== (string) Auth::id(), 403);

        if ($dto->personnelAction === 'add_page') {
            $result = $this->personnelInstanceService->addPage($report);

            return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        if ($dto->personnelAction && str_starts_with($dto->personnelAction, 'remove_page_')) {
            $pageNum = (int) str_replace('remove_page_', '', $dto->personnelAction);
            $result = $this->personnelInstanceService->removePage($report, $pageNum);

            return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        if ($dto->action !== 'save') {
            return back()->with('error', 'Aksi ini bukan simpan draft.');
        }

        $invalidFields = $this->entryService->validateCfu($dto->entries);
        if (! empty($invalidFields)) {
            return back()
                ->withInput()
                ->withErrors(['cfu' => 'Terdapat ' . count($invalidFields) . ' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC. Nilai nol, desimal, dan negatif tidak diperbolehkan.']);
        }

        $request = request();
        $this->entryService->process($request, $report);

        $this->workflowService->recordParticipation($report, (string) Auth::id());

        return back()->with('success', 'Data berhasil disimpan sebagai draft.');
    }
}
