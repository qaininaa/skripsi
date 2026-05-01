<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\LocationRequest;
use App\Models\ReportLocation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $roomId = $request->input('room_id');

        $locations = ReportLocation::with(['room', 'section'])
            ->when($search, fn ($q) => $q->whereHas('room', fn ($q) => $q
                ->where('room_name', 'like', "%{$search}%")
                ->orWhere('room_number', 'like', "%{$search}%")
            )->orWhere('location_number', 'like', "%{$search}%"))
            ->when($roomId, fn ($q) => $q->where('room_id', $roomId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $rooms = Room::orderBy('room_name')->get();

        return view('pages.master.location.index', compact('locations', 'rooms'));
    }

    public function create(): View
    {
        $rooms = Room::orderBy('class')->orderBy('room_name')->get();

        return view('pages.master.location.create', compact('rooms'));
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        ReportLocation::create($request->validated());

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(ReportLocation $location): View
    {
        $rooms = Room::orderBy('class')->orderBy('room_name')->get();

        return view('pages.master.location.edit', compact('location', 'rooms'));
    }

    public function update(LocationRequest $request, ReportLocation $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(ReportLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}