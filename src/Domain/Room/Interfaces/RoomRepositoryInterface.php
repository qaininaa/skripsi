<?php

namespace Domain\Room\Interfaces;

use Domain\Room\Dtos\CreateRoomDto;
use Domain\Room\Dtos\UpdateRoomDto;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for room data access.
 */
interface RoomRepositoryInterface
{
    /**
     * Retrieve a paginated list of rooms with optional filters.
     *
     * @return LengthAwarePaginator<int, Room>
     */
    public function paginateForManagement(?string $search, ?string $class, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find duplicate room by room name (case-insensitive).
     */
    public function findDuplicateByName(string $roomName, ?string $ignoreRoomId = null): ?Room;

    /**
     * Persist a new room from DTO.
     */
    public function create(CreateRoomDto $dto): Room;

    /**
     * Update an existing room from DTO.
     */
    public function update(Room $room, UpdateRoomDto $dto): Room;

    /**
     * Check whether the room has related locations.
     */
    public function hasLocations(Room $room): bool;

    /**
     * Delete a room record.
     */
    public function delete(Room $room): void;
}
