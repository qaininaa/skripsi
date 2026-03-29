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

        return view('dashboard.tugas-pelaporan.index', compact('tugas'));
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

        return view('dashboard.tugas-pelaporan.create', compact(
            'analis', 'reportTypes', 'instruments', 'instrumentMap'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'              => ['required', 'date'],
            'nama_produk'          => ['required', 'string', 'max:255'],
            'nomor_batch_produk'   => ['required', 'string', 'max:255'],
            'shift1_analis_id'     => ['required', 'exists:users,id'],
            'shift2_analis_id'     => ['nullable', 'exists:users,id', 'different:shift1_analis_id'],
            'report_type_id'       => ['required', 'exists:report_types,id'],
        ]);

        Report::create([
            'report_type_id'     => $request->report_type_id,
            'tanggal'            => $request->tanggal,
            'nama_produk'        => $request->nama_produk,
            'nomor_batch_produk' => $request->nomor_batch_produk,
            'shift1_analis_id'   => $request->shift1_analis_id,
            'shift2_analis_id'   => $request->shift2_analis_id,
            'created_by'         => Auth::id(),
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

        return view('dashboard.tugas-pelaporan.edit', compact(
            'tugasPelaporan', 'analis', 'reportTypes', 'selectedReportType',
            'instruments', 'instrumentMap', 'selectedInstrument'
        ));
    }

    public function update(Request $request, Report $tugasPelaporan)
    {
        $request->validate([
            'tanggal'            => ['required', 'date'],
            'nama_produk'        => ['required', 'string', 'max:255'],
            'nomor_batch_produk' => ['required', 'string', 'max:255'],
            'shift1_analis_id'   => ['required', 'exists:users,id'],
            'shift2_analis_id'   => ['nullable', 'exists:users,id', 'different:shift1_analis_id'],
            'report_type_id'     => ['required', 'exists:report_types,id'],
        ]);

        $tugasPelaporan->update([
            'report_type_id'     => $request->report_type_id,
            'tanggal'            => $request->tanggal,
            'nama_produk'        => $request->nama_produk,
            'nomor_batch_produk' => $request->nomor_batch_produk,
            'shift1_analis_id'   => $request->shift1_analis_id,
            'shift2_analis_id'   => $request->shift2_analis_id,
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
