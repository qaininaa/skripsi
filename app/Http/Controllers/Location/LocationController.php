<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\LocationStoreRequest;
use App\Http\Requests\Location\LocationUpdateRequest;
use Domain\Location\Models\Location;
use Domain\Location\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for master location management actions.
 */
class LocationController extends Controller
{
    public function __construct(private LocationService $locationService) {}

    /**
     * Show paginated location list with optional filters.
     */
    public function index(Request $request): View
    {
        $locations = $this->locationService->paginateForManagement(
            $request->input('search'),
            $request->input('room_id'),
        );

        $rooms = $this->locationService->roomOptionsForIndex();

        return view('pages.master.location.index', compact('locations', 'rooms'));
    }

    /**
     * Show location create form.
     */
    public function create(): View
    {
        $rooms = $this->locationService->roomOptionsForForm();

        return view('pages.master.location.create', compact('rooms'));
    }

    /**
     * Persist a new location from validated DTO.
     */
    public function store(LocationStoreRequest $request): RedirectResponse
    {
        $dto = $request->toDTO();

        $duplicate = $this->locationService->findDuplicate($dto->roomId, $dto->locationNumber);

        if ($duplicate) {
            return redirect()
                ->route('master.location.edit', $duplicate)
                ->with('info', 'Lokasi sudah ada. Anda dapat mengubah data lokasi tersebut di sini.');
        }

        $this->locationService->createLocation($dto);

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    /**
     * Show location edit form.
     */
    public function edit(Location $location): View
    {
        $rooms = $this->locationService->roomOptionsForForm();

        return view('pages.master.location.edit', compact('location', 'rooms'));
    }

    /**
     * Update an existing location from validated DTO.
     */
    public function update(LocationUpdateRequest $request, Location $location): RedirectResponse
    {
        $dto = $request->toDTO();

        $duplicate = $this->locationService->findDuplicate($dto->roomId, $dto->locationNumber, $location->id);

        if ($duplicate) {
            return back()
                ->withErrors(['duplicate' => 'Nomor lokasi sudah ada. Gunakan nomor lokasi lain.'])
                ->withInput();
        }

        $this->locationService->updateLocation($location, $dto);

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    /**
     * Delete a location record.
     */
    public function destroy(Location $location): RedirectResponse
    {
        $this->locationService->deleteLocation($location);

        return redirect()
            ->route('master.location.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
