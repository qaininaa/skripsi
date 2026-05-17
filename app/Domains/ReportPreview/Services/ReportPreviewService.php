<?php

namespace App\Domains\ReportPreview\Services;

use App\Domains\Report\Models\Report;
use App\Services\SectionInstanceService;

/**
 * Service for report preview structure actions.
 */
class ReportPreviewService
{
    public function __construct(
        private SectionInstanceService $sectionInstanceService,
    ) {}

    /**
     * Duplicate one section instance in preview context.
     *
     * @param Report $report
     * @param string $sectionId
     * @return array{ok: bool, message: string}
     */
    public function duplicateSection(Report $report, string $sectionId): array
    {
        return $this->sectionInstanceService->duplicate($report, $sectionId);
    }

    /**
     * Remove one duplicated section instance in preview context.
     *
     * @param Report $report
     * @param string $sectionId
     * @return array{ok: bool, message: string}
     */
    public function removeSection(Report $report, string $sectionId): array
    {
        return $this->sectionInstanceService->remove($report, $sectionId);
    }
}