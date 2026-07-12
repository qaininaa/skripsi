<?php

namespace Domain\Room\Services;

use Domain\AuditLog\Services\AuditLogService;
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
    public function __construct(
        private RoomRepositoryInterface $repository,
        private AuditLogService $auditLogService,
    ) {}

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
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function createRoom(CreateRoomDto $dto, array $meta = []): Room
    {
        $room = $this->repository->create($dto);

        $this->auditLogService->log(
            'create_room',
            "{$this->actorLabel($meta)} menambah ruangan: {$room->room_name} ({$room->room_number}) kelas {$room->class}",
            $meta
        );

        return $room;
    }

    /**
     * Update an existing room from DTO.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function updateRoom(Room $room, UpdateRoomDto $dto, array $meta = []): Room
    {
        $oldInfo = "{$room->room_name} ({$room->room_number}) kelas {$room->class}";
        $updatedRoom = $this->repository->update($room, $dto);

        $this->auditLogService->log(
            'update_room',
            "{$this->actorLabel($meta)} mengubah ruangan: {$oldInfo} menjadi {$updatedRoom->room_name} ({$updatedRoom->room_number}) kelas {$updatedRoom->class}",
            $meta
        );

        return $updatedRoom;
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
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function deleteRoom(Room $room, array $meta = []): void
    {
        $roomInfo = "{$room->room_name} ({$room->room_number}) kelas {$room->class}";

        $this->repository->delete($room);

        $this->auditLogService->log(
            'delete_room',
            "{$this->actorLabel($meta)} menghapus ruangan: {$roomInfo}",
            $meta
        );
    }

    /**
     * @param  array{actor_username?: string|null}  $meta
     */
    private function actorLabel(array $meta): string
    {
        return $meta['actor_username'] ?? 'User';
    }
}
