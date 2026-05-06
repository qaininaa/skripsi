<?php

namespace App\Domains\Room\Repositories;

use App\Domains\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomRepository
{
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

    public function findDuplicate(array $validated): ?Room
    {
        return Room::query()
            ->where('room_name', $validated['room_name'])
            ->where('room_number', $validated['room_number'])
            ->first();
    }

    public function create(array $validated): Room
    {
        return Room::create($validated);
    }

    public function update(Room $room, array $validated): Room
    {
        $room->update($validated);

        return $room;
    }

    public function hasLocations(Room $room): bool
    {
        return $room->locations()->exists();
    }

    public function delete(Room $room): void
    {
        $room->delete();
    }
}
