<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomStoreRequest;
use App\Http\Requests\Room\RoomUpdateRequest;
use Domain\Room\Models\Room;
use Domain\Room\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for master room management actions.
 */
class RoomController extends Controller
{
    public function __construct(private RoomService $roomService) {}

    /**
     * Show paginated room list with optional filters.
     */
    public function index(Request $request): View
    {
        $rooms = $this->roomService->paginateForManagement(
            $request->input('search'),
            $request->input('class'),
        );

        return view('pages.master.room.index', compact('rooms'));
    }

    /**
     * Show room create form.
     */
    public function create(): View
    {
        return view('pages.master.room.create');
    }

    /**
     * Persist a new room from validated DTO.
     */
    public function store(RoomStoreRequest $request): RedirectResponse
    {
        $dto = $request->toDTO();

        $duplicate = $this->roomService->findDuplicateByName($dto->roomName);

        if ($duplicate) {
            return redirect()
                ->route('master.room.edit', $duplicate)
                ->with('info', 'Nama ruangan sudah ada. Anda dapat mengubah data ruangan tersebut di sini.');
        }

        $this->roomService->createRoom($dto);

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Show room edit form.
     */
    public function edit(Room $room): View
    {
        return view('pages.master.room.edit', [
            'ruangan' => $room,
        ]);
    }

    /**
     * Update an existing room from validated DTO.
     */
    public function update(RoomUpdateRequest $request, Room $room): RedirectResponse
    {
        $dto = $request->toDTO();

        $duplicate = $this->roomService->findDuplicateByName($dto->roomName, $room->id);

        if ($duplicate) {
            return back()
                ->withErrors(['room_name' => 'Nama ruangan sudah ada. Gunakan nama ruangan lain.'])
                ->withInput();
        }

        $this->roomService->updateRoom($room, $dto);

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    /**
     * Delete a room when no locations are linked.
     */
    public function destroy(Room $room): RedirectResponse
    {
        if ($this->roomService->hasLocations($room)) {
            return back()->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki data lokasi.');
        }

        $this->roomService->deleteRoom($room);

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
