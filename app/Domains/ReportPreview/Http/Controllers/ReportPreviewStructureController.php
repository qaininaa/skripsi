<?php

namespace App\Domains\ReportPreview\Http\Controllers;

use App\Domains\ReportPreview\Services\ReportPreviewService;
use App\Http\Controllers\Controller;
use App\Domains\Report\Models\Report;
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
     *
     * @param Report $report
     * @param string $sectionId
     * @return mixed
     */
    public function duplicateSection(Report $report, string $sectionId): mixed
    {
        $this->ensureAdminAccess();

        $result = $this->previewService->duplicateSection($report, $sectionId);

        return $this->respond($result);
    }

    /**
     * Remove duplicated section instance from admin preview page.
     *
     * @param Report $report
     * @param string $sectionId
     * @return mixed
     */
    public function removeSection(Report $report, string $sectionId): mixed
    {
        $this->ensureAdminAccess();

        $result = $this->previewService->removeSection($report, $sectionId);

        return $this->respond($result);
    }

    /**
     * Ensure current user is Admin QC.
     *
     * @return void
     */
    private function ensureAdminAccess(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    /**
     * Return JSON or redirect response based on request type.
     *
     * @param array{ok: bool, message: string} $result
     * @return mixed
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
