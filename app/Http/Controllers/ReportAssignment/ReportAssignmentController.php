<?php

namespace App\Http\Controllers\ReportAssignment;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportAssignment\ReportAssignmentStoreRequest;
use App\Http\Requests\ReportAssignment\ReportAssignmentUpdateRequest;
use Domain\ReportAssignment\Models\ReportAssignment;
use Domain\ReportAssignment\Services\ReportAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for admin report assignment actions.
 */
class ReportAssignmentController extends Controller
{
    public function __construct(private ReportAssignmentService $assignmentService) {}

    /**
     * Show paginated assignment list with optional filters.
     */
    public function index(Request $request): View
    {
        $reportAssignments = $this->assignmentService->paginateForManagement(
            $request->input('search'),
            $request->input('status'),
        );

        return view('pages.report-assignment.index', compact('reportAssignments'));
    }

    /**
     * Show assignment create form.
     */
    public function create(): View
    {
        $reportTypes = $this->assignmentService->reportTypeOptions();

        return view('pages.report-assignment.create', compact('reportTypes'));
    }

    /**
     * Persist a new report assignment.
     */
    public function store(ReportAssignmentStoreRequest $request): RedirectResponse
    {
        $meta = $this->meta($request);
        $createdBy = (string) ($request->user()?->id ?? '');

        $this->assignmentService->createAssignment(
            $request->toDTO(),
            $createdBy,
            $meta
        );

        return redirect()
            ->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }

    /**
     * Show assignment edit form.
     */
    public function edit(ReportAssignment $report): View
    {
        $reportTypes = $this->assignmentService->reportTypeOptions();

        return view('pages.report-assignment.edit', [
            'reportAssignment' => $report,
            'reportTypes' => $reportTypes,
        ]);
    }

    /**
     * Update an existing assignment.
     */
    public function update(ReportAssignmentUpdateRequest $request, ReportAssignment $report): RedirectResponse
    {
        $this->assignmentService->updateAssignment($report, $request->toDTO(), $this->meta($request));

        return redirect()
            ->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil diperbarui.');
    }

    /**
     * Delete a pending assignment.
     */
    public function destroy(Request $request, ReportAssignment $report): RedirectResponse
    {
        $deleted = $this->assignmentService->deletePendingAssignment($report, $this->meta($request));

        if (! $deleted) {
            return back()->with('error', 'Tugas tidak dapat dihapus karena sudah dikerjakan.');
        }

        return redirect()
            ->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil dihapus.');
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
}
