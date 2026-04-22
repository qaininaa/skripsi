<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $class = $request->input('class');

        $rooms = Room::when($search, fn ($q) => $q->where(function ($q) use ($search) {
            $q->where('room_name', 'like', "%{$search}%")
                ->orWhere('room_number', 'like', "%{$search}%")
                ->orWhere('class', 'like', "%{$search}%");
        }))
            ->when($class, fn ($q) => $q->where('class', $class))
            ->withCount('locations')
            ->orderBy('class', 'asc')
            ->orderBy('room_name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('pages.master.ruangan.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('pages.master.ruangan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:50'],
        ]);

        $existing = Room::where('room_name', $validated['room_name'])
            ->where('room_number', $validated['room_number'])
            ->first();

        if ($existing) {
            return redirect()
                ->route('master.ruangan.edit', $existing)
                ->with('info', 'Ruangan dengan nama dan nomor yang sama sudah ada. Anda dapat mengubahnya di sini.');
        }

        Room::create($validated);

        return redirect()
            ->route('master.ruangan.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $ruangan): View
    {
        return view('pages.master.ruangan.edit', compact('ruangan'));
    }

    public function update(Request $request, Room $ruangan): RedirectResponse
    {
        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:50'],
        ]);

        $ruangan->update($validated);

        return redirect()
            ->route('master.ruangan.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $ruangan): RedirectResponse
    {
        if ($ruangan->locations()->exists()) {
            return back()->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki data lokasi.');
        }

        $ruangan->delete();

        return redirect()
            ->route('master.ruangan.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
