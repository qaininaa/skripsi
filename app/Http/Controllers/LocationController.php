<?php

namespace App\Http\Controllers;

use App\Models\Frequency;
use App\Models\ReportLocation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $search  = $request->input('search');
        $roomId  = $request->input('room_id');

        $locations = ReportLocation::with(['room', 'frequency'])
            ->when($search, fn($q) => $q->whereHas('room', fn($q) => $q
                ->where('room_name', 'like', "%{$search}%")
                ->orWhere('room_number', 'like', "%{$search}%")
            )->orWhere('location_number', 'like', "%{$search}%"))
            ->when($roomId, fn($q) => $q->where('id_room', $roomId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $rooms = Room::orderBy('room_name')->get();

        return view('pages.master.lokasi.index', compact('locations', 'rooms'));
    }

    public function create(): View
    {
        $rooms       = Room::orderBy('room_name')->get();
        $frequencies = Frequency::orderBy('name')->get();

        return view('pages.master.lokasi.create', compact('rooms', 'frequencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_room'               => ['required', 'exists:rooms,id'],
            'frequency_id'          => ['nullable', 'exists:frequencies,id'],
            'location_number'       => ['nullable', 'string', 'max:50'],
            'measurement_type'      => ['nullable', 'string', 'max:50'],
            'alert_limit_total'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_limit_fungi'   => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_action_total'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_action_fungi'  => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        ReportLocation::create($validated);

        return redirect()
            ->route('master.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(ReportLocation $lokasi): View
    {
        $rooms       = Room::orderBy('room_name')->get();
        $frequencies = Frequency::orderBy('name')->get();

        return view('pages.master.lokasi.edit', compact('lokasi', 'rooms', 'frequencies'));
    }

    public function update(Request $request, ReportLocation $lokasi): RedirectResponse
    {
        $validated = $request->validate([
            'id_room'               => ['required', 'exists:rooms,id'],
            'frequency_id'          => ['nullable', 'exists:frequencies,id'],
            'location_number'       => ['nullable', 'string', 'max:50'],
            'measurement_type'      => ['nullable', 'string', 'max:50'],
            'alert_limit_total'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_limit_fungi'   => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_action_total'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_action_fungi'  => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $lokasi->update($validated);

        return redirect()
            ->route('master.lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(ReportLocation $lokasi): RedirectResponse
    {
        $lokasi->delete();

        return redirect()
            ->route('master.lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
