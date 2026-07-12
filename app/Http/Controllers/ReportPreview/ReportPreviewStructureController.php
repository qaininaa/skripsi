<?php

namespace App\Http\Controllers\ReportPreview;

use App\Http\Controllers\Controller;
use App\Services\ReportPreview\ReportPreviewService;
use Domain\Report\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for report structure actions from admin preview context.
 */
class ReportPreviewStructureController extends Controller
{
    public function __construct(private ReportPreviewService $previewService) {}

    /**
     * Duplicate section instance from admin preview page.
     */
    public function duplicateSection(Request $request, Report $report, string $sectionId): JsonResponse|RedirectResponse
    {
        $this->ensureAdminAccess();

        return $this->respond($request, $this->previewService->duplicateSection($report, $sectionId, $this->meta($request)));
    }

    /**
     * Remove duplicated section instance from admin preview page.
     */
    public function removeSection(Request $request, Report $report, string $sectionId): JsonResponse|RedirectResponse
    {
        $this->ensureAdminAccess();

        return $this->respond($request, $this->previewService->removeSection($report, $sectionId, $this->meta($request)));
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    /**
     * Build audit log meta from current request.
     *
     * @return array{user_id: string|null, ip_address: string|null, user_agent: string|null, actor_username: string|null}
     */
    private function meta(Request $request): array
    {
        return [
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'actor_username' => $request->user()?->username,
        ];
    }

    /**
     * @param  array{ok: bool, message: string}  $result
     */
    private function respond(Request $request, array $result): JsonResponse|RedirectResponse
    {
        $status = $result['ok'] ? 200 : 422;

        if ($request->wantsJson()) {
            return response()->json($result, $status);
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}
