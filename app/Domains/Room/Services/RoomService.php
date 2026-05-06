<?php

namespace App\Domains\Room\Services;

use App\Domains\Room\Models\Room;
use App\Domains\Room\Repositories\RoomRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomService
{
    public function __construct(private RoomRepository $repository) {}

    /**
     * Get paginated room list for management page.
     *
     * @return LengthAwarePaginator<int, Room>
     */
    public function paginateForManagement(?string $search, ?string $class): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $class, 15);
    }

    /**
     * Find duplicate room by identifying attributes.
     *
     * @param  array<string, mixed>  $validated
     */
    public function findDuplicate(array $validated): ?Room
    {
        return $this->repository->findDuplicate($validated);
    }

    /**
     * Create a room record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Room
    {
        return $this->repository->create($validated);
    }

    /**
     * Update room record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Room $room, array $validated): Room
    {
        return $this->repository->update($room, $validated);
    }

    /**
     * Check whether room has linked locations.
     */
    public function hasLocations(Room $room): bool
    {
        return $this->repository->hasLocations($room);
    }

    /**
     * Delete room record.
     */
    public function delete(Room $room): void
    {
        $this->repository->delete($room);
    }
}
