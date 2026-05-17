<?php

namespace App\Services\ReportPreview;

use App\Services\SectionInstanceService;
use Domain\Report\Models\Report;

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
     * @return array{ok: bool, message: string}
     */
    public function duplicateSection(Report $report, string $sectionId): array
    {
        return $this->sectionInstanceService->duplicate($report, $sectionId);
    }

    /**
     * Remove one duplicated section instance in preview context.
     *
     * @return array{ok: bool, message: string}
     */
    public function removeSection(Report $report, string $sectionId): array
    {
        return $this->sectionInstanceService->remove($report, $sectionId);
    }
}
