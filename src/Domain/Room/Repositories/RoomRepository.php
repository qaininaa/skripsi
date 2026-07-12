<?php

namespace Domain\Room\Repositories;

use Domain\Room\Dtos\CreateRoomDto;
use Domain\Room\Dtos\UpdateRoomDto;
use Domain\Room\Interfaces\RoomRepositoryInterface;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of RoomRepositoryInterface.
 */
class RoomRepository implements RoomRepositoryInterface
{
    /**
     * Retrieve paginated rooms with optional search and class filters.
     *
     * @return LengthAwarePaginator<int, Room>
     */
    public function paginateForManagement(?string $search, ?string $class, int $perPage = 15): LengthAwarePaginator
    {
        return Room::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%")
                    ->orWhere('class', 'like', "%{$search}%");
            }))
            ->when($class, fn ($q) => $q->where('class', $class))
            ->withCount('locations')
            ->orderBy('class')
            ->orderBy('room_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find duplicate room by room name (case-insensitive).
     */
    public function findDuplicateByName(string $roomName, ?string $ignoreRoomId = null): ?Room
    {
        $normalizedName = mb_strtolower(trim($roomName));

        return Room::query()
            ->when($ignoreRoomId, fn ($q) => $q->whereKeyNot($ignoreRoomId))
            ->whereRaw('LOWER(room_name) = ?', [$normalizedName])
            ->first();
    }

    /**
     * Persist a new room record.
     */
    public function create(CreateRoomDto $dto): Room
    {
        return Room::create($dto->toArray());
    }

    /**
     * Update an existing room record.
     */
    public function update(Room $room, UpdateRoomDto $dto): Room
    {
        $room->update($dto->toArray());

        return $room;
    }

    /**
     * Check whether the room has related locations.
     */
    public function hasLocations(Room $room): bool
    {
        return $room->locations()->exists();
    }

    /**
     * Delete a room record.
     */
    public function delete(Room $room): void
    {
        $room->delete();
    }
}
