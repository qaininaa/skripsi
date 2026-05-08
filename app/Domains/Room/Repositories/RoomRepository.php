<?php

namespace App\Domains\Room\Repositories;

use App\Domains\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomRepository
{
    /**
     * Get paginated room list with optional search and class filter.
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
     *
     * @param  array<string, mixed>  $validated
     */
    public function findDuplicate(array $validated, ?string $ignoreRoomId = null): ?Room
    {
        $normalizedName = mb_strtolower(trim((string) $validated['room_name']));

        return Room::query()
            ->when($ignoreRoomId, fn ($q) => $q->whereKeyNot($ignoreRoomId))
            ->whereRaw('LOWER(room_name) = ?', [$normalizedName])
            ->first();
    }

    /**
     * Create a room record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Room
    {
        return Room::create($validated);
    }

    /**
     * Update a room record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Room $room, array $validated): Room
    {
        $room->update($validated);

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
