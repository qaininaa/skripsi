<?php

namespace Domain\ReportType\Repositories;

use Domain\Location\Models\Location;
use Domain\ReportType\Interfaces\SectionLocationRepositoryInterface;
use Domain\ReportType\Models\ReportSection;

/**
 * Eloquent implementation of SectionLocationRepositoryInterface.
 */
class SectionLocationRepository implements SectionLocationRepositoryInterface
{
    /**
     * Find location by ID or throw exception.
     */
    public function findLocationOrFail(string $locationId): Location
    {
        return Location::query()->findOrFail($locationId);
    }

    /**
     * Assign a location to a report section.
     */
    public function assignToSection(Location $location, ReportSection $section): void
    {
        $location->update([
            'section_id' => $section->id,
            'section_assigned_at' => now(),
        ]);
    }

    /**
     * Clear section assignment from a location.
     */
    public function clearSection(Location $location): void
    {
        $location->update([
            'section_id' => null,
            'section_assigned_at' => null,
        ]);
    }
}
