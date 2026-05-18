<?php

namespace Domain\Location\Repositories;

use Domain\Location\Dtos\CreateLocationDto;
use Domain\Location\Dtos\UpdateLocationDto;
use Domain\Location\Interfaces\LocationRepositoryInterface;
use Domain\Location\Models\Location;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of LocationRepositoryInterface.
 */
class LocationRepository implements LocationRepositoryInterface
{
    /**
     * Retrieve paginated locations with optional search and room filter.
     *
     * @return LengthAwarePaginator<int, Location>
     */
    public function paginateForManagement(?string $search, ?string $roomId, int $perPage = 15): LengthAwarePaginator
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
     * Fetch room options for create/edit form.
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
     * Find duplicate location by room id and location number.
     */
    public function findDuplicate(string $roomId, string $locationNumber, ?string $ignoreLocationId = null): ?Location
    {
        return Location::query()
            ->when($ignoreLocationId, fn ($q) => $q->whereKeyNot($ignoreLocationId))
            ->where('room_id', $roomId)
            ->where('location_number', $locationNumber)
            ->first();
    }

    /**
     * Load room relation needed for business descriptions.
     */
    public function withRoom(Location $location): Location
    {
        return $location->loadMissing('room');
    }

    /**
     * Persist a new location record.
     */
    public function create(CreateLocationDto $dto): Location
    {
        return Location::create($dto->toArray())->load('room');
    }

    /**
     * Update an existing location record.
     */
    public function update(Location $location, UpdateLocationDto $dto): Location
    {
        $location->update($dto->toArray());

        return $location->refresh()->load('room');
    }

    /**
     * Delete a location record.
     */
    public function delete(Location $location): void
    {
        $location->delete();
    }
}
