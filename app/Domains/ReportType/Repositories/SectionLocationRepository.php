<?php

namespace App\Domains\ReportType\Repositories;

use App\Domains\Location\Models\Location;
use App\Domains\ReportType\Models\ReportSection;

class SectionLocationRepository
{
    public function findLocationOrFail(string $locationId): Location
    {
        return Location::query()->findOrFail($locationId);
    }

    public function assignToSection(Location $location, ReportSection $section): void
    {
        $location->update(['section_id' => $section->id]);
    }

    public function clearSection(Location $location): void
    {
        $location->update(['section_id' => null]);
    }
}
