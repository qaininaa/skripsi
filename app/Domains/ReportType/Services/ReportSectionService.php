<?php

namespace App\Domains\ReportType\Services;

use App\Domains\ReportType\Models\ReportSection;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Repositories\ReportSectionRepository;

class ReportSectionService
{
    public function __construct(private ReportSectionRepository $repository) {}

    public function create(ReportType $reportType, array $validated, bool $hasMachineSetup): ReportSection
    {
        $payload = $validated;
        $payload['report_type_id'] = $reportType->id;
        $payload['order'] = $this->repository->nextOrder($reportType);
        $payload['has_machine_setup'] = $hasMachineSetup;

        return $this->repository->create($payload);
    }

    public function update(ReportSection $section, array $validated, bool $hasMachineSetup): ReportSection
    {
        $payload = $validated;
        $payload['has_machine_setup'] = $hasMachineSetup;

        return $this->repository->update($section, $payload);
    }

    public function delete(ReportSection $section): void
    {
        $this->repository->delete($section);
    }
}
