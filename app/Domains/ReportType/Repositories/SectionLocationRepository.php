<?php

namespace App\Domains\ReportType\Repositories;

use App\Domains\ReportType\Models\ReportSection;
use Domain\Location\Models\Location;

/**
 * Repository for section-location assignment persistence.
 */
class SectionLocationRepository
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
