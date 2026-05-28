<?php

namespace Domain\Report\Services;

use App\Services\Reports\ReportWorkflowService;
use Domain\Report\Dtos\ReportDraftingSaveDto;
use Domain\Report\Models\AnalystReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service for analyst report drafting flow (draft-only actions).
 */
class ReportDraftingService
{
    public function __construct(
        private ReportEntryService $entryService,
        private EnvironmentalEntryService $environmentalEntryService,
        private ReportWorkflowService $workflowService,
    ) {}

    /**
     * Save report form payload as draft only.
     */
    public function saveDraft(AnalystReport $report, ReportDraftingSaveDto $dto, Request $request): RedirectResponse
    {
        abort_if(in_array($report->status, ['submitted', 'approved'], true), 403);
        abort_if((string) $report->locked_by !== (string) Auth::id(), 403);

        if ($dto->action !== 'save') {
            return back()->with('error', 'Aksi ini bukan simpan draft.');
        }

        $invalidFields = $this->environmentalEntryService->validateCfu($dto->entries);
        if (! empty($invalidFields)) {
            return back()
                ->withInput()
                ->with('focus_input', $invalidFields[0])
                ->withErrors(['cfu' => 'Terdapat ' . count($invalidFields) . ' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat 1 sampai 200, <1, atau TNTC. Nilai nol, desimal, negatif, dan angka di atas 200 tidak diperbolehkan.']);
        }

        $this->entryService->process($request, $report);

        $this->workflowService->recordParticipation($report, (string) Auth::id());

        return back()->with('success', 'Data berhasil disimpan sebagai draft.');
    }
}
