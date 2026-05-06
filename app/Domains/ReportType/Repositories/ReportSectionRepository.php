<?php

namespace App\Domains\ReportType\Repositories;

use App\Domains\ReportType\Models\ReportSection;
use App\Domains\ReportType\Models\ReportType;

class ReportSectionRepository
{
    public function nextOrder(ReportType $reportType): int
    {
        return (int) (($reportType->sections()->max('order') ?? 0) + 1);
    }

    public function create(array $payload): ReportSection
    {
        return ReportSection::create($payload);
    }

    public function update(ReportSection $section, array $payload): ReportSection
    {
        $section->update($payload);

        return $section;
    }

    public function delete(ReportSection $section): void
    {
        $section->delete();
    }
}
