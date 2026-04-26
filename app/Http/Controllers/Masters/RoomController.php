<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreRoomRequest;
use App\Http\Requests\Masters\UpdateRoomRequest;
use App\Models\Room;
use App\Services\Masters\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(private RoomService $service) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $class  = $request->input('class');

        $rooms = Room::when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%")
                    ->orWhere('class', 'like', "%{$search}%");
            }))
            ->when($class, fn ($q) => $q->where('class', $class))
            ->withCount('locations')
            ->orderBy('class')
            ->orderBy('room_name')
            ->paginate(15)
            ->withQueryString();

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
                ->with('info', 'Ruangan dengan nama dan nomor yang sama sudah ada. Anda dapat mengubahnya di sini.');
        }

        $this->service->create($request->validated());

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room): View
    {
        return view('pages.master.room.edit', compact('room'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $this->service->update($room, $request->validated());

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->locations()->exists()) {
            return back()->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki data lokasi.');
        }

        $this->service->delete($room);

        return redirect()
            ->route('master.room.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}