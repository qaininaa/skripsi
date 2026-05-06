<?php

namespace App\Domains\Room\Services;

use App\Domains\Room\Models\Room;
use App\Domains\Room\Repositories\RoomRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomService
{
    public function __construct(private RoomRepository $repository) {}

    public function paginateForManagement(?string $search, ?string $class): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $class, 15);
    }

    public function findDuplicate(array $validated): ?Room
    {
        return $this->repository->findDuplicate($validated);
    }

    public function create(array $validated): Room
    {
        return $this->repository->create($validated);
    }

    public function update(Room $room, array $validated): Room
    {
        return $this->repository->update($room, $validated);
    }

    public function hasLocations(Room $room): bool
    {
        return $this->repository->hasLocations($room);
    }

    public function delete(Room $room): void
    {
        $this->repository->delete($room);
    }
}
