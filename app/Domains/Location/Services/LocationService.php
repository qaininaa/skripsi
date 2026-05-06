<?php

namespace App\Domains\Location\Services;

use App\Domains\Location\Models\Location;
use App\Domains\Location\Repositories\LocationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationService
{
    public function __construct(private LocationRepository $repository) {}

    public function paginateForManagement(?string $search, ?string $roomId): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $roomId, 15);
    }

    public function roomOptionsForIndex(): Collection
    {
        return $this->repository->roomOptionsForIndex();
    }

    public function roomOptionsForForm(): Collection
    {
        return $this->repository->roomOptionsForForm();
    }

    public function create(array $validated): Location
    {
        return $this->repository->create($validated);
    }

    public function update(Location $location, array $validated): Location
    {
        return $this->repository->update($location, $validated);
    }

    public function delete(Location $location): void
    {
        $this->repository->delete($location);
    }
}
