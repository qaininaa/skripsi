<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasPelaporanController extends Controller
{
    public function index()
    {
        $tugas = Report::with(['reportType', 'shift1Analis', 'shift2Analis', 'createdBy'])
            ->latest()
            ->paginate(15);

        return view('pages.tugas-pelaporan.index', compact('tugas'));
    }

    public function create()
    {
        $analis      = User::where('role', 'analis')->orderBy('name')->get();
        $reportTypes = ReportType::where('is_active', true)->orderBy('annex_number')->get();

        // Instrument list selalu tetap 4, meski belum semua punya jenis laporan
        $instruments = ['air_sampler', 'settle_plate', 'contact_plate', 'swab'];

        // Map: instrument => [report_type_id, ...]
        $instrumentMap = $reportTypes->groupBy('instrument')
            ->map(fn($items) => $items->pluck('id')->values());

        return view('pages.tugas-pelaporan.create', compact(
            'analis', 'reportTypes', 'instruments', 'instrumentMap'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date'          => ['required', 'date'],
            'product_name'         => ['required', 'string', 'max:255'],
            'batch_number'         => ['required', 'string', 'max:255'],
            'shift1_analyst_id'    => ['required', 'exists:users,id'],
            'shift2_analyst_id'    => ['nullable', 'exists:users,id', 'different:shift1_analyst_id'],
            'report_type_id'       => ['required', 'exists:report_types,id'],
        ]);

        Report::create([
            'report_type_id'   => $request->report_type_id,
            'report_date'      => $request->report_date,
            'product_name'     => $request->product_name,
            'batch_number'     => $request->batch_number,
            'shift1_analyst_id' => $request->shift1_analyst_id,
            'shift2_analyst_id' => $request->shift2_analyst_id,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }

    public function edit(Report $tugasPelaporan)
    {
        $analis      = User::where('role', 'analis')->orderBy('name')->get();
        $reportTypes = ReportType::where('is_active', true)->orderBy('annex_number')->get();

        $instruments = ['air_sampler', 'settle_plate', 'contact_plate', 'swab'];
        $instrumentMap = $reportTypes->groupBy('instrument')
            ->map(fn($items) => $items->pluck('id')->values());

        $selectedReportType = $tugasPelaporan->report_type_id;
        $selectedInstrument = $tugasPelaporan->reportType->instrument;

        return view('pages.tugas-pelaporan.edit', compact(
            'tugasPelaporan', 'analis', 'reportTypes', 'selectedReportType',
            'instruments', 'instrumentMap', 'selectedInstrument'
        ));
    }

    public function update(Request $request, Report $tugasPelaporan)
    {
        $request->validate([
            'report_date'        => ['required', 'date'],
            'product_name'       => ['required', 'string', 'max:255'],
            'batch_number'       => ['required', 'string', 'max:255'],
            'shift1_analyst_id'  => ['required', 'exists:users,id'],
            'shift2_analyst_id'  => ['nullable', 'exists:users,id', 'different:shift1_analyst_id'],
            'report_type_id'     => ['required', 'exists:report_types,id'],
        ]);

        $tugasPelaporan->update([
            'report_type_id'    => $request->report_type_id,
            'report_date'       => $request->report_date,
            'product_name'      => $request->product_name,
            'batch_number'      => $request->batch_number,
            'shift1_analyst_id' => $request->shift1_analyst_id,
            'shift2_analyst_id' => $request->shift2_analyst_id,
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
