<?php

namespace App\Domains\Location\Services;

use App\Domains\Location\Models\Location;
use App\Domains\Location\Repositories\LocationRepository;
use Domain\Room\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationService
{
    public function __construct(private LocationRepository $repository) {}

    /**
     * Get paginated locations for management page.
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
     * Find duplicate location by room ID and location number.
     *
     * @param  array<string, mixed>  $validated
     */
    public function findDuplicate(array $validated, ?string $ignoreLocationId = null): ?Location
    {
        return $this->repository->findDuplicate($validated, $ignoreLocationId);
    }

    /**
     * Create a location record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Location
    {
        return $this->repository->create($validated);
    }

    /**
     * Update a location record.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Location $location, array $validated): Location
    {
        return $this->repository->update($location, $validated);
    }

    /**
     * Delete a location record.
     */
    public function delete(Location $location): void
    {
        $this->repository->delete($location);
    }
}
