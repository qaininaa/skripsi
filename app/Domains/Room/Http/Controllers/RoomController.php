<?php

namespace App\Domains\Room\Http\Controllers;

use App\Domains\Room\Http\Requests\RoomRequest;
use App\Domains\Room\Models\Room;
use App\Domains\Room\Services\RoomService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(private RoomService $service) {}

    public function index(Request $request): View
    {
        $rooms = $this->service->paginateForManagement(
            $request->input('search'),
            $request->input('class')
        );

        return view('pages.master.room.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('pages.master.room.create');
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $duplicate = $this->service->findDuplicate($request->validated());

        if ($duplicate) {
            return redirect()
                ->route('master.room.edit', $duplicate)
                ->with('info', 'Nama ruangan sudah ada. Anda dapat mengubah data ruangan tersebut di sini.');
        }

        $this->service->create($request->validated());

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room): View
    {
        return view('pages.master.room.edit', [
            'ruangan' => $room,
        ]);
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $duplicate = $this->service->findDuplicate($request->validated(), $room->id);

        if ($duplicate) {
            return back()
                ->withErrors(['room_name' => 'Nama ruangan sudah ada. Gunakan nama ruangan lain.'])
                ->withInput();
        }

        $this->service->update($room, $request->validated());

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($this->service->hasLocations($room)) {
            return back()->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki data lokasi.');
        }

        $this->service->delete($room);

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
