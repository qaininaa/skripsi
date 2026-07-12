<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportDraftingSaveRequest;
use App\Http\Requests\Report\ReportPasswordVerifyRequest;
use Domain\Report\Models\AnalystReport;
use Domain\Report\Services\ReportDraftingService;
use Domain\Report\Services\ReportSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for analyst report drafting flow.
 */
class ReportDraftingController extends Controller
{
    public function __construct(
        private ReportDraftingService $draftingService,
        private ReportSubmissionService $submissionService,
    ) {}

    /**
     * Save report payload and perform selected drafting action.
     */
    public function save(ReportDraftingSaveRequest $request, AnalystReport $report): RedirectResponse
    {
        $dto = $request->toDTO();

        if ($dto->action === 'save') {
            return $this->draftingService->saveDraft($report, $dto, $request);
        }

        return $this->submissionService->saveAndApplyAction($report, $dto, $request);
    }

    /**
     * Verify analyst credentials via AJAX before sensitive actions.
     */
    public function verifyPassword(ReportPasswordVerifyRequest $request): JsonResponse
    {
        $result = $this->submissionService->verifyCredentials(
            (string) $request->input('username'),
            (string) $request->input('password')
        );

        if (($result['ok'] ?? false) === false) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
