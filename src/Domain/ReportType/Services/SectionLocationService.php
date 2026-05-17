<?php

namespace Domain\ReportType\Services;

use Domain\Location\Models\Location;
use Domain\ReportType\Dtos\AssignSectionLocationDto;
use Domain\ReportType\Interfaces\SectionLocationRepositoryInterface;
use Domain\ReportType\Models\ReportSection;

/**
 * Service for assigning and detaching locations to report sections.
 */
class SectionLocationService
{
    public function __construct(private SectionLocationRepositoryInterface $repository) {}

    /**
     * Attach location to section if not already used by another section.
     *
     * @return array{success: bool, message: string}
     */
    public function attach(ReportSection $section, AssignSectionLocationDto $dto): array
    {
        $location = $this->repository->findLocationOrFail($dto->locationId);

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

    /**
     * Detach location from section if assignment matches.
     */
    public function detach(ReportSection $section, Location $location): void
    {
        if ((string) $location->section_id === (string) $section->id) {
            $this->repository->clearSection($location);
        }
    }
}
