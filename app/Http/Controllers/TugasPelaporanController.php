<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasPelaporanController extends Controller
{
    public function index(Request $request)
    {
        $query  = $request->input('search');
        $status = $request->input('status');

        $tugas = Report::with(['reportType', 'createdBy'])
            ->when($query, fn($q) => $q->where(function ($q) use ($query) {
                $q->where('product_name', 'like', "%{$query}%")
                  ->orWhere('batch_number',  'like', "%{$query}%");
            }))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tugas-pelaporan.index', compact('tugas'));
    }

    public function create()
    {
        $reportTypes = ReportType::orderBy('annex_number')->get();

        return view('pages.tugas-pelaporan.create', compact('reportTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name'   => ['required', 'string', 'max:255'],
            'batch_number'   => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        Report::create([
            'report_type_id' => $request->report_type_id,
            'product_name'   => $request->product_name,
            'batch_number'   => $request->batch_number,
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }

    public function edit(Report $tugasPelaporan)
    {
        $reportTypes = ReportType::orderBy('annex_number')->get();

        return view('pages.tugas-pelaporan.edit', compact('tugasPelaporan', 'reportTypes'));
    }

    public function update(Request $request, Report $tugasPelaporan)
    {
        $request->validate([
            'product_name'   => ['required', 'string', 'max:255'],
            'batch_number'   => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        $hd = $tugasPelaporan->header_data ?? [];
        // If report type changed, clear section counts (they're tied to the old type's sections)
        if ((int) $request->report_type_id !== (int) $tugasPelaporan->report_type_id) {
            unset($hd['_section_counts']);
        }

        $tugasPelaporan->update([
            'report_type_id' => $request->report_type_id,
            'product_name'   => $request->product_name,
            'batch_number'   => $request->batch_number,
            'header_data'    => empty($hd) ? null : $hd,
        ]);

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil diperbarui.');
    }

    public function destroy(Report $tugasPelaporan)
    {
        if ($tugasPelaporan->status !== 'pending') {
            return back()->with('error', 'Tugas tidak dapat dihapus karena sudah dikerjakan.');
        }

        $tugasPelaporan->delete();

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil dihapus.');
    }

    public function duplicateSection(Report $report, $sectionId)
    {
        abort_unless(
            $report->reportType->sections->contains('id', (int) $sectionId),
            404
        );

        $hd      = $report->header_data ?? [];
        $counts  = $hd['_section_counts'] ?? [];
        $current = (int) ($counts[$sectionId] ?? 1);

        if ($current >= 5) {
            return back()->with('error', 'Maksimum 5 instance per seksi.');
        }

        $counts[(int) $sectionId] = $current + 1;
        $hd['_section_counts']    = $counts;
        $report->update(['header_data' => $hd]);

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Seksi berhasil diduplikat.');
    }

    public function removeSection(Report $report, $sectionId)
    {
        abort_unless(
            $report->reportType->sections->contains('id', (int) $sectionId),
            404
        );

        $hd      = $report->header_data ?? [];
        $counts  = $hd['_section_counts'] ?? [];
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
