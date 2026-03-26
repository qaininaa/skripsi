<?php

namespace App\Http\Controllers;

use App\Models\TugasPelaporan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasPelaporanController extends Controller
{
    public function index()
    {
        $tugas = TugasPelaporan::with(['shift1Analis', 'shift2Analis'])->latest()->paginate(15);

        return view('dashboard.tugas-pelaporan.index', compact('tugas'));
    }

    public function create()
    {
        $analis = User::where('role', 'analis')->orderBy('name')->get();

        return view('dashboard.tugas-pelaporan.create', compact('analis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'        => ['required', 'date'],
            'shift1_analis_id' => ['required', 'exists:users,id'],
            'shift2_analis_id' => ['required', 'exists:users,id', 'different:shift1_analis_id'],
        ]);

        TugasPelaporan::create([
            'tanggal'          => $request->tanggal,
            'shift1_analis_id' => $request->shift1_analis_id,
            'shift2_analis_id' => $request->shift2_analis_id,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('tugas-pelaporan.index')
            ->with('success', 'Tugas pelaporan berhasil ditambahkan.');
    }
}
