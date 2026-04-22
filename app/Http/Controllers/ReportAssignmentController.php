<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles report assignment management by Admin QC.
 *
 * Responsibilities:
 * - Create, edit, delete report tasks (tugas pelaporan)
 * - Search and filter report list
 * - Manage section duplication per report
 */
class ReportAssignmentController extends Controller
{
    /**
     * Display a paginated list of all report assignments.
     * Supports filtering by product name / batch number and report status.
     *
     * @param  Request  $request  Contains optional 'search' and 'status' query params
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->input('search');
        $status = $request->input('status');

        $tugas = Report::with(['reportType', 'createdBy'])
            ->when($query, fn ($q) => $q->where(function ($q) use ($query) {
                $q->where('product_name', 'like', "%{$query}%")
                    ->orWhere('batch_number', 'like', "%{$query}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tugas-pelaporan.index', compact('tugas'));
    }

    /**
     * Show the form to create a new report assignment.
     * Loads available report types for the dropdown.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $reportTypes = ReportType::orderBy('annex_number')->get();

        return view('pages.tugas-pelaporan.create', compact('reportTypes'));
    }

    /**
     * Validate and save a new report assignment to the database.
     * Sets the current admin as the creator (created_by).
     *
     * @param  Request  $request  Must contain: product_name, batch_number, report_type_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'batch_number' => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        Report::create([
            'report_type_id' => $request->report_type_id,
            'product_name' => $request->product_name,
            'batch_number' => $request->batch_number,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }

    /**
     * Show the form to edit an existing report assignment.
     * Loads available report types for the dropdown.
     *
     * @param  Report  $tugasPelaporan  The report to be edited (route model binding)
     * @return \Illuminate\View\View
     */
    public function edit(Report $tugasPelaporan)
    {
        $reportTypes = ReportType::orderBy('annex_number')->get();

        return view('pages.tugas-pelaporan.edit', compact('tugasPelaporan', 'reportTypes'));
    }

    /**
     * Validate and update an existing report assignment.
     * If the report type changes, clears section counts from header_data
     * because they are tied to the old type's sections.
     *
     * @param  Request  $request  Must contain: product_name, batch_number, report_type_id
     * @param  Report  $tugasPelaporan  The report to be updated (route model binding)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Report $tugasPelaporan)
    {
        $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'batch_number' => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        $hd = $tugasPelaporan->header_data ?? [];
        // If report type changed, clear section counts (they're tied to the old type's sections)
        if ((int) $request->report_type_id !== (int) $tugasPelaporan->report_type_id) {
            unset($hd['_section_counts']);
        }

        $tugasPelaporan->update([
            'report_type_id' => $request->report_type_id,
            'product_name' => $request->product_name,
            'batch_number' => $request->batch_number,
            'header_data' => empty($hd) ? null : $hd,
        ]);

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil diperbarui.');
    }

    /**
     * Delete a report assignment.
     * Only allowed if the report is still in 'pending' status
     * (has not been worked on by an analyst yet).
     *
     * @param  Report  $tugasPelaporan  The report to be deleted (route model binding)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Report $tugasPelaporan)
    {
        if ($tugasPelaporan->status !== 'pending') {
            return back()->with('error', 'Tugas tidak dapat dihapus karena sudah dikerjakan.');
        }

        $tugasPelaporan->delete();

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil dihapus.');
    }

    /**
     * Increment the instance count of a section in a report.
     * Used when a section needs to be measured multiple times (max 5 instances).
     * Stores the count in header_data['_section_counts'].
     *
     * @param  Report  $report  The target report
     * @param  int  $sectionId  The section to duplicate
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function duplicateSection(Report $report, $sectionId)
    {
        abort_unless(
            $report->reportType->sections->contains('id', (int) $sectionId),
            404
        );

        $hd = $report->header_data ?? [];
        $counts = $hd['_section_counts'] ?? [];
        $current = (int) ($counts[$sectionId] ?? 1);

        if ($current >= 5) {
            return back()->with('error', 'Maksimum 5 instance per seksi.');
        }

        $counts[(int) $sectionId] = $current + 1;
        $hd['_section_counts'] = $counts;
        $report->update(['header_data' => $hd]);

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Seksi berhasil diduplikat.');
    }

    /**
     * Decrement the instance count of a duplicated section.
     * Removes the count entry entirely when it drops back to 1 (default).
     *
     * @param  Report  $report  The target report
     * @param  int  $sectionId  The section to reduce
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function removeSection(Report $report, $sectionId)
    {
        abort_unless(
            $report->reportType->sections->contains('id', (int) $sectionId),
            404
        );

        $hd = $report->header_data ?? [];
        $counts = $hd['_section_counts'] ?? [];
        $current = (int) ($counts[$sectionId] ?? 1);

        if ($current <= 2) {
            unset($counts[(int) $sectionId]);
        } else {
            $counts[(int) $sectionId] = $current - 1;
        }

        if (empty($counts)) {
            unset($hd['_section_counts']);
        } else {
            $hd['_section_counts'] = $counts;
        }

        $report->update(['header_data' => empty($hd) ? null : $hd]);

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Duplikasi seksi berhasil dihapus.');
    }
}
