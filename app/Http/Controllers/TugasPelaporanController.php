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
        $reportTypes        = ReportType::orderBy('annex_number')->get();
        $selectedReportType = $tugasPelaporan->report_type_id;

        return view('pages.tugas-pelaporan.edit', compact(
            'tugasPelaporan', 'reportTypes', 'selectedReportType'
        ));
    }

    public function update(Request $request, Report $tugasPelaporan)
    {
        $request->validate([
            'product_name'   => ['required', 'string', 'max:255'],
            'batch_number'   => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ]);

        $tugasPelaporan->update([
            'report_type_id' => $request->report_type_id,
            'product_name'   => $request->product_name,
            'batch_number'   => $request->batch_number,
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
}
