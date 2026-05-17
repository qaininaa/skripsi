<?php

namespace App\Domains\Report\Http\Controllers;

use App\Domains\Report\DTOs\ReportDraftingSaveDTO;
use App\Domains\Report\Models\AnalystReport;
use App\Domains\Report\Services\ReportDraftingService;
use App\Domains\Report\Services\ReportSubmissionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
    public function save(Request $request, AnalystReport $report): RedirectResponse
    {
        $dto = ReportDraftingSaveDTO::fromRequest($request);

        if ($dto->action === 'save') {
            return $this->draftingService->saveDraft($report, $dto);
        }

        return $this->submissionService->saveAndApplyAction($report, $dto);
    }

    /**
     * Verify analyst credentials via AJAX before sensitive actions.
     */
    public function verifyPassword(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

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
