<?php

namespace Domain\ReportType\Services;

use Domain\ReportType\Dtos\CreateReportSectionDto;
use Domain\ReportType\Dtos\UpdateReportSectionDto;
use Domain\ReportType\Interfaces\ReportSectionRepositoryInterface;
use Domain\ReportType\Models\ReportSection;
use Domain\ReportType\Models\ReportType;

/**
 * Service for report section management use-cases.
 */
class ReportSectionService
{
    public function __construct(private ReportSectionRepositoryInterface $repository) {}

    /**
     * Create a section and assign next display order in report type.
     */
    public function createSection(ReportType $reportType, CreateReportSectionDto $dto): ReportSection
    {
        $payload = $dto->toArray();
        $payload['report_type_id'] = $reportType->id;
        $payload['order'] = $this->repository->nextOrder($reportType);

        return $this->repository->create($payload);
    }

    /**
     * Update an existing section.
     */
    public function updateSection(ReportSection $section, UpdateReportSectionDto $dto): ReportSection
    {
        return $this->repository->update($section, $dto->toArray());
    }

    /**
     * Delete a section record.
     */
    public function deleteSection(ReportSection $section): void
    {
        $this->repository->delete($section);
    }
}
