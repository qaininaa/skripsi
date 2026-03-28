<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalisLaporanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $userId = Auth::id();

        $query = Report::with(['reportType', 'shift1Analis', 'shift2Analis'])
            ->where(fn($q) => $q->where('shift1_analis_id', $userId)
                                ->orWhere('shift2_analis_id', $userId))
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(15)->withQueryString();

        // Hitung badge per status
        $counts = Report::where(fn($q) => $q->where('shift1_analis_id', $userId)
                                             ->orWhere('shift2_analis_id', $userId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.laporan.index', compact('items', 'status', 'counts'));
    }
}
