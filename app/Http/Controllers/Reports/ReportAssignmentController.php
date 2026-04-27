<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportType;
use App\Services\Reports\Sections\SectionInstanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ReportAssignmentController
 *
 * Tanggung jawab: kelola penugasan laporan oleh AdminQC.
 *   - Buat, edit, hapus tugas laporan (product name, batch, report type)
 *   - Duplikasi section sebelum laporan dikerjakan analis
 *
 * Lokasi: app/Http/Controllers/Report/ReportAssignmentController.php
 */
class ReportAssignmentController extends Controller
{
    public function __construct(
        private SectionInstanceService $sectionInstanceService
    ) {}

    /**
     * GET /report-assignment
     * Daftar semua tugas laporan dengan filter search dan status.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $reportAssignments = Report::with(['reportType', 'createdBy'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.report-assignment.index', compact('reportAssignments'));
    }

    /**
     * GET /report-assignment/create
     * Form buat tugas laporan baru.
     */
    public function create()
    {
        $reportTypes = ReportType::orderBy('annex_number')->get();

        return view('pages.report-assignment.create', compact('reportTypes'));
    }

    /**
     * POST /report-assignment
     * Simpan tugas laporan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name'   => ['required', 'string', 'max:255'],
            'batch_number'   => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        $report = Report::create([
            'report_type_id' => $request->report_type_id,
            'product_name'   => $request->product_name,
            'batch_number'   => $request->batch_number,
            'created_by'     => Auth::id(),
        ]);

        $this->sectionInstanceService->ensureInstancesInitialized($report);

        return redirect()->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }

    /**
     * GET /report-assignment/{reportAssignment}/edit
     * Form edit tugas laporan.
     */
    public function edit(Report $reportAssignment)
    {
        $reportTypes = ReportType::orderBy('annex_number')->get();

        return view('pages.report-assignment.edit', compact('reportAssignment', 'reportTypes'));
    }

    /**
     * PUT /report-assignment/{reportAssignment}
     * Update tugas laporan.
     */
    public function update(Request $request, Report $reportAssignment)
    {
        $request->validate([
            'product_name'   => ['required', 'string', 'max:255'],
            'batch_number'   => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        $reportAssignment->update([
            'report_type_id' => $request->report_type_id,
            'product_name'   => $request->product_name,
            'batch_number'   => $request->batch_number,
        ]);

        $this->sectionInstanceService->ensureInstancesInitialized($reportAssignment->fresh());

        return redirect()->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil diperbarui.');
    }

    /**
     * DELETE /report-assignment/{reportAssignment}
     * Hapus tugas laporan.
     * Hanya bisa dihapus kalau masih status 'pending' (belum dikerjakan analis).
     */
    public function destroy(Report $reportAssignment)
    {
        if ($reportAssignment->status !== 'pending') {
            return back()->with('error', 'Tugas tidak dapat dihapus karena sudah dikerjakan.');
        }

        $reportAssignment->delete();

        return redirect()->route('report-assignment.index')
            ->with('success', 'Tugas pelaporan berhasil dihapus.');
    }

    /**
     * POST /report-assignment/{report}/sections/{sectionId}/duplicate
     * Tambah instance duplikat section (maks 5).
     * Didelegasikan ke SectionInstanceService — logic yang sama dipakai analis juga.
     */
    public function duplicateSection(Report $report, string $sectionId)
    {
        $result = $this->sectionInstanceService->duplicate($report, $sectionId);

        return request()->wantsJson()
            ? response()->json($result, $result['ok'] ? 200 : 422)
            : ($result['ok']
                ? back()->with('success', $result['message'])
                : back()->with('error', $result['message']));
    }

    /**
     * DELETE /report-assignment/{report}/sections/{sectionId}/duplicate
     * Hapus satu instance duplikat section.
     * Didelegasikan ke SectionInstanceService — logic yang sama dipakai analis juga.
     */
    public function removeSection(Report $report, string $sectionId)
    {
        $result = $this->sectionInstanceService->remove($report, $sectionId);

        return request()->wantsJson()
            ? response()->json($result, $result['ok'] ? 200 : 422)
            : ($result['ok']
                ? back()->with('success', $result['message'])
                : back()->with('error', $result['message']));
    }
}

