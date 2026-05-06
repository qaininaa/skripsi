<?php

namespace App\Domains\Location\Repositories;

use App\Domains\Location\Models\Location;
use App\Domains\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationRepository
{
    public function paginateForManagement(?string $search, ?string $roomId, int $perPage = 10): LengthAwarePaginator
    {
        return Location::query()
            ->with(['room', 'section'])
            ->when($search, fn ($q) => $q
                ->whereHas('room', fn ($q) => $q
                    ->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%")
                )
                ->orWhere('location_number', 'like', "%{$search}%")
            )
            ->when($roomId, fn ($q) => $q->where('room_id', $roomId))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function roomOptionsForIndex(): Collection
    {
        return Room::query()->orderBy('room_name')->get();
    }

    public function roomOptionsForForm(): Collection
    {
        return Room::query()
            ->orderBy('class')
            ->orderBy('room_name')
            ->get();
    }

    public function create(array $validated): Location
    {
        return Location::create($validated);
    }

    public function update(Location $location, array $validated): Location
    {
        $location->update($validated);

        return $location;
    }

    public function delete(Location $location): void
    {
        $location->delete();
    }
}
