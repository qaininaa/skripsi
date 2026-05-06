<?php

namespace App\Domains\ReportPreview\Services;

use App\Models\Report;
use App\Services\PersonnelInstanceService;
use App\Services\SectionInstanceService;

/**
 * Service for report preview structure actions.
 */
class ReportPreviewService
{
    public function __construct(
        private SectionInstanceService $sectionInstanceService,
        private PersonnelInstanceService $personnelInstanceService,
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

    /**
     * Handle personnel page add/remove action from preview context.
     *
     * @param Report $report
     * @param string|null $action
     * @return array{ok: bool, message: string}
     */
    public function handlePersonnelPageAction(Report $report, ?string $action): array
    {
        if ($action === 'add_page') {
            return $this->personnelInstanceService->addPage($report);
        }

        if (str_starts_with((string) $action, 'remove_page_')) {
            $pageNum = (int) str_replace('remove_page_', '', (string) $action);

            return $this->personnelInstanceService->removePage($report, $pageNum);
        }

        return ['ok' => false, 'message' => 'Aksi tidak dikenali.'];
    }
}