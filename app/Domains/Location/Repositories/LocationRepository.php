<?php

namespace App\Domains\Location\Repositories;

use App\Domains\Location\Models\Location;
use App\Domains\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationRepository
{
    /**
     * Get paginated locations with optional search and room filter.
     *
     * @return LengthAwarePaginator<int, Location>
     */
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

    /**
     * Fetch room options for index filter.
     *
     * @return Collection<int, Room>
     */
    public function roomOptionsForIndex(): Collection
    {
        return Room::query()->orderBy('room_name')->get();
    }

    /**
     * Fetch room options for location create/edit form.
     *
     * @return Collection<int, Room>
     */
    public function roomOptionsForForm(): Collection
    {
        return Room::query()
            ->orderBy('class')
            ->orderBy('room_name')
            ->get();
    }

    /**
     * Create a location record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Location
    {
        return Location::create($validated);
    }

    /**
     * Update an existing location record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Location $location, array $validated): Location
    {
        $location->update($validated);

        return $location;
    }

    /**
     * Delete a location record.
     */
    public function delete(Location $location): void
    {
        $location->delete();
    }
}
