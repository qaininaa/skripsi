<?php

namespace App\Domains\ReportType\Services;

use App\Domains\ReportType\Models\ReportSection;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Repositories\ReportSectionRepository;

/**
 * Service for report section management use-cases.
 */
class SectionService
{
    public function __construct(private ReportSectionRepository $repository) {}

    /**
     * Create a section and assign next display order in report type.
     *
     * @param array{
     *   measurement_unit: string,
     *   measurement_type: string,
     *   max_column: int,
     *   column_label?: string|null,
     *   time_slot_type: string,
     *   has_machine_setup?: bool
     * } $validated
     */
    public function create(ReportType $reportType, array $validated, bool $hasMachineSetup): ReportSection
    {
        $payload = $validated;
        $payload['report_type_id'] = $reportType->id;
        $payload['order'] = $this->repository->nextOrder($reportType);
        $payload['has_machine_setup'] = $hasMachineSetup;

        return $this->repository->create($payload);
    }

    /**
     * Update an existing section.
     *
     * @param array{
     *   measurement_unit: string,
     *   measurement_type: string,
     *   max_column: int,
     *   column_label?: string|null,
     *   time_slot_type: string,
     *   has_machine_setup?: bool,
     *   order?: int
     * } $validated
     */
    public function update(ReportSection $section, array $validated, bool $hasMachineSetup): ReportSection
    {
        $payload = $validated;
        $payload['has_machine_setup'] = $hasMachineSetup;

        return $this->repository->update($section, $payload);
    }

    /**
     * Delete section record.
     */
    public function delete(ReportSection $section): void
    {
        $this->repository->delete($section);
    }
}
