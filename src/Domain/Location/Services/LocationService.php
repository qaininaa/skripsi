<?php

namespace Domain\Location\Services;

use Domain\AuditLog\Services\AuditLogService;
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
    public function __construct(
        private LocationRepositoryInterface $repository,
        private AuditLogService $auditLogService,
    ) {}

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
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function createLocation(CreateLocationDto $dto, array $meta = []): Location
    {
        $location = $this->repository->create($dto);

        $this->auditLogService->log(
            'create_location',
            "{$this->actorLabel($meta)} menambah lokasi: {$this->locationLabel($location)}",
            $meta
        );

        return $location;
    }

    /**
     * Update an existing location record.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function updateLocation(Location $location, UpdateLocationDto $dto, array $meta = []): Location
    {
        $location = $this->repository->withRoom($location);
        $oldInfo = $this->locationLabel($location);
        $updatedLocation = $this->repository->update($location, $dto);

        $this->auditLogService->log(
            'update_location',
            "{$this->actorLabel($meta)} mengubah lokasi: {$oldInfo} menjadi {$this->locationLabel($updatedLocation)}",
            $meta
        );

        return $updatedLocation;
    }

    /**
     * Delete a location record.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     */
    public function deleteLocation(Location $location, array $meta = []): void
    {
        $location = $this->repository->withRoom($location);
        $locationInfo = $this->locationLabel($location);

        $this->repository->delete($location);

        $this->auditLogService->log(
            'delete_location',
            "{$this->actorLabel($meta)} menghapus lokasi: {$locationInfo}",
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

    private function locationLabel(Location $location): string
    {
        $roomName = $location->room?->room_name ?? 'Ruangan tidak diketahui';
        $roomNumber = $location->room?->room_number ?? '-';
        $frequency = Location::frequencyLabel($location->frequency);
        $measurementType = $location->getFormattedMeasurementType();

        return "{$roomName} ({$roomNumber}) - No. {$location->location_number}, {$frequency}, {$measurementType}";
    }
}
