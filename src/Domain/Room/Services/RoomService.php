<?php

namespace Domain\Room\Services;

use Domain\Room\Dtos\CreateRoomDto;
use Domain\Room\Dtos\UpdateRoomDto;
use Domain\Room\Interfaces\RoomRepositoryInterface;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Handles business logic for room management.
 */
class RoomService
{
    public function __construct(private RoomRepositoryInterface $repository) {}

    /**
     * Retrieve paginated rooms for management page.
     *
     * @return LengthAwarePaginator<int, Room>
     */
    public function paginateForManagement(?string $search, ?string $class): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $class, 15);
    }

    /**
     * Find duplicate room by room name (case-insensitive).
     */
    public function findDuplicateByName(string $roomName, ?string $ignoreRoomId = null): ?Room
    {
        return $this->repository->findDuplicateByName($roomName, $ignoreRoomId);
    }

    /**
     * Create a room record from DTO.
     */
    public function createRoom(CreateRoomDto $dto): Room
    {
        return $this->repository->create($dto);
    }

    /**
     * Update an existing room from DTO.
     */
    public function updateRoom(Room $room, UpdateRoomDto $dto): Room
    {
        return $this->repository->update($room, $dto);
    }

    /**
     * Check whether room has linked locations.
     */
    public function hasLocations(Room $room): bool
    {
        return $this->repository->hasLocations($room);
    }

    /**
     * Delete a room record.
     */
    public function deleteRoom(Room $room): void
    {
        $this->repository->delete($room);
    }
}
