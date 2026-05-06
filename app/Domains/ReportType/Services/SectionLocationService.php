<?php

namespace App\Domains\ReportType\Services;

use App\Domains\Location\Models\Location;
use App\Domains\ReportType\Models\ReportSection;
use App\Domains\ReportType\Repositories\SectionLocationRepository;

class SectionLocationService
{
    public function __construct(private SectionLocationRepository $repository) {}

    public function attach(ReportSection $section, string $locationId): array
    {
        $location = $this->repository->findLocationOrFail($locationId);

        if ($location->section_id && (string) $location->section_id !== (string) $section->id) {
            return [
                'success' => false,
                'message' => 'Lokasi sudah terhubung ke section lain. Hapus terlebih dahulu dari section asal.',
            ];
        }

        if ((string) $location->section_id !== (string) $section->id) {
            $this->repository->assignToSection($location, $section);
        }

        return [
            'success' => true,
            'message' => 'Lokasi berhasil ditambahkan.',
        ];
    }

    public function detach(ReportSection $section, Location $location): void
    {
        if ((string) $location->section_id === (string) $section->id) {
            $this->repository->clearSection($location);
        }
    }
}
