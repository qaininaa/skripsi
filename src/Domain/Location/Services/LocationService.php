<?php

namespace Domain\Location\Services;

use Domain\Location\Dtos\CreateLocationDto;
use Domain\Location\Dtos\UpdateLocationDto;
use Domain\Location\Interfaces\LocationRepositoryInterface;
use Domain\Location\Models\Location;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Handles business logic for location management.
 */
class LocationService
{
    public function __construct(private LocationRepositoryInterface $repository) {}

    /**
     * Retrieve paginated locations for management page.
     *
     * @return LengthAwarePaginator<int, Location>
     */
    public function paginateForManagement(?string $search, ?string $roomId): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $roomId, 15);
    }

    /**
     * Get room options for index filter.
     *
     * @return Collection<int, Room>
     */
    public function roomOptionsForIndex(): Collection
    {
        return $this->repository->roomOptionsForIndex();
    }

    /**
     * Get room options for create/edit form.
     *
     * @return Collection<int, Room>
     */
    public function roomOptionsForForm(): Collection
    {
        return $this->repository->roomOptionsForForm();
    }

    /**
     * Find duplicate location by room id and location number.
     */
    public function findDuplicate(string $roomId, string $locationNumber, ?string $ignoreLocationId = null): ?Location
    {
        return $this->repository->findDuplicate($roomId, $locationNumber, $ignoreLocationId);
    }

    /**
     * Create a new location record.
     */
    public function createLocation(CreateLocationDto $dto): Location
    {
        return $this->repository->create($dto);
    }

    /**
     * Update an existing location record.
     */
    public function updateLocation(Location $location, UpdateLocationDto $dto): Location
    {
        return $this->repository->update($location, $dto);
    }

    /**
     * Delete a location record.
     */
    public function deleteLocation(Location $location): void
    {
        $this->repository->delete($location);
    }
}
