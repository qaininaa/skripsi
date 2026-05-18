<?php

namespace Domain\Location\Interfaces;

use Domain\Location\Dtos\CreateLocationDto;
use Domain\Location\Dtos\UpdateLocationDto;
use Domain\Location\Models\Location;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contract for location data access.
 */
interface LocationRepositoryInterface
{
    /**
     * Retrieve a paginated list of locations with optional filters.
     *
     * @return LengthAwarePaginator<int, Location>
     */
    public function paginateForManagement(?string $search, ?string $roomId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Fetch room options for filter dropdown.
     *
     * @return Collection<int, Room>
     */
    public function roomOptionsForIndex(): Collection;

    /**
     * Fetch room options for create/edit form.
     *
     * @return Collection<int, Room>
     */
    public function roomOptionsForForm(): Collection;

    /**
     * Find duplicate location by room id and location number.
     */
    public function findDuplicate(string $roomId, string $locationNumber, ?string $ignoreLocationId = null): ?Location;

    /**
     * Load room relation needed for business descriptions.
     */
    public function withRoom(Location $location): Location;

    /**
     * Persist a new location from DTO.
     */
    public function create(CreateLocationDto $dto): Location;

    /**
     * Update an existing location from DTO.
     */
    public function update(Location $location, UpdateLocationDto $dto): Location;

    /**
     * Delete a location record.
     */
    public function delete(Location $location): void;
}
