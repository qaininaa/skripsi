<?php

namespace App\Http\Controllers\ReportPreview;

use App\Http\Controllers\Controller;
use App\Services\ReportPreview\ReportPreviewService;
use Domain\Report\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for report structure actions from admin preview context.
 */
class ReportPreviewStructureController extends Controller
{
    public function __construct(private ReportPreviewService $previewService) {}

    /**
     * Duplicate section instance from admin preview page.
     */
    public function duplicateSection(Report $report, string $sectionId): JsonResponse|RedirectResponse
    {
        $this->ensureAdminAccess();

        return $this->respond($this->previewService->duplicateSection($report, $sectionId));
    }

    /**
     * Remove duplicated section instance from admin preview page.
     */
    public function removeSection(Report $report, string $sectionId): JsonResponse|RedirectResponse
    {
        $this->ensureAdminAccess();

        return $this->respond($this->previewService->removeSection($report, $sectionId));
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    /**
     * @param  array{ok: bool, message: string}  $result
     */
    private function respond(array $result): JsonResponse|RedirectResponse
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
