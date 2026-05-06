<?php

namespace App\Domains\Location\Http\Controllers;

use App\Domains\Location\Http\Requests\LocationRequest;
use App\Domains\Location\Models\Location;
use App\Domains\Location\Services\LocationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(private LocationService $service) {}

    public function index(Request $request): View
    {
        $locations = $this->service->paginateForManagement(
            $request->input('search'),
            $request->input('room_id')
        );

        $rooms = $this->service->roomOptionsForIndex();

        return view('pages.master.location.index', compact('locations', 'rooms'));
    }

    public function create(): View
    {
        $rooms = $this->service->roomOptionsForForm();

        return view('pages.master.location.create', compact('rooms'));
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location): View
    {
        $rooms = $this->service->roomOptionsForForm();

        return view('pages.master.location.edit', compact('location', 'rooms'));
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $this->service->update($location, $request->validated());

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $this->service->delete($location);

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
