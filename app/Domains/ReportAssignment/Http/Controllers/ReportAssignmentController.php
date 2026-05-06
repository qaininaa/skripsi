<?php

namespace App\Domains\ReportAssignment\Http\Controllers;

use App\Domains\ReportAssignment\DTOs\ReportAssignmentDTO;
use App\Domains\ReportAssignment\Http\Requests\ReportAssignmentRequest;
use App\Domains\ReportAssignment\Models\ReportAssignment;
use App\Domains\ReportAssignment\Services\ReportAssignmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller for admin report assignment actions only.
 */
class ReportAssignmentController extends Controller
{
    public function __construct(private ReportAssignmentService $assignmentService) {}

    /**
     * Show assignment list with optional filters.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $reportAssignments = $this->assignmentService->paginateForManagement($search, $status);

        return view('pages.report-assignment.index', compact('reportAssignments'));
    }

    /**
     * Show assignment create form.
     *
     * @return View
     */
    public function create()
    {
        $reportTypes = $this->assignmentService->reportTypeOptions();

        return view('pages.report-assignment.create', compact('reportTypes'));
    }

    /**
     * Store new report assignment.
     *
     * @return RedirectResponse
     */
    public function store(ReportAssignmentRequest $request)
    {
        $dto = ReportAssignmentDTO::fromArray($request->validated());
        $this->assignmentService->createAssignment($dto, (string) Auth::id());

        return redirect()->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }

    /**
     * Show assignment edit form.
     *
     * @return View
     */
    public function edit(ReportAssignment $reportAssignment)
    {
        $reportTypes = $this->assignmentService->reportTypeOptions();

        return view('pages.report-assignment.edit', compact('reportAssignment', 'reportTypes'));
    }

    /**
     * Update an assignment.
     *
     * @return RedirectResponse
     */
    public function update(ReportAssignmentRequest $request, ReportAssignment $reportAssignment)
    {
        $dto = ReportAssignmentDTO::fromArray($request->validated());
        $this->assignmentService->updateAssignment($reportAssignment, $dto);

        return redirect()->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil diperbarui.');
    }

    /**
     * Delete assignment when still pending.
     *
     * @return RedirectResponse
     */
    public function destroy(ReportAssignment $reportAssignment)
    {
        $deleted = $this->assignmentService->deletePendingAssignment($reportAssignment);

        if (! $deleted) {
            return back()->with('error', 'Tugas tidak dapat dihapus karena sudah dikerjakan.');
        }

        return redirect()->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil dihapus.');
    }
}
