<?php

namespace App\Services\Masters;

use App\Models\Room;

class RoomService
{
    public function findDuplicate(array $validated): ?Room
    {
        return Room::where('room_name', $validated['room_name'])
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

    public function delete(Room $room): void
    {
        $room->delete();
    }
}