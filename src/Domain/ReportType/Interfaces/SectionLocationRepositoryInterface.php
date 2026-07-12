<?php

namespace Domain\ReportType\Interfaces;

use Domain\Location\Models\Location;
use Domain\ReportType\Models\ReportSection;

/**
 * Contract for section-location assignment persistence.
 */
interface SectionLocationRepositoryInterface
{
    /**
     * Find location by ID or throw exception.
     */
    public function findLocationOrFail(string $locationId): Location;

    /**
     * Assign a location to a report section.
     */
    public function assignToSection(Location $location, ReportSection $section): void;

    /**
     * Clear section assignment from a location.
     */
    public function clearSection(Location $location): void;
}
