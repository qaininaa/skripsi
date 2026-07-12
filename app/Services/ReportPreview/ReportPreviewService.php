<?php

namespace App\Services\ReportPreview;

use App\Services\SectionInstanceService;
use Domain\AuditLog\Services\AuditLogService;
use Domain\Report\Models\Report;

/**
 * Service for report preview structure actions.
 */
class ReportPreviewService
{
    public function __construct(
        private SectionInstanceService $sectionInstanceService,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Duplicate one section instance in preview context.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     * @return array{ok: bool, message: string}
     */
    public function duplicateSection(Report $report, string $sectionId, array $meta = []): array
    {
        $result = $this->sectionInstanceService->duplicate($report, $sectionId);

        if ($result['ok']) {
            $this->auditLogService->log(
                'duplicate_report_section',
                "{$this->actorLabel($meta)} menduplikasi {$this->sectionLabel($report, $sectionId)} pada tugas pelaporan {$this->reportLabel($report)}",
                $meta
            );
        }

        return $result;
    }

    /**
     * Remove one duplicated section instance in preview context.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null, actor_username?: string|null}  $meta
     * @return array{ok: bool, message: string}
     */
    public function removeSection(Report $report, string $sectionId, array $meta = []): array
    {
        $result = $this->sectionInstanceService->remove($report, $sectionId);

        if ($result['ok']) {
            $this->auditLogService->log(
                'remove_report_section_duplicate',
                "{$this->actorLabel($meta)} menghapus duplikasi {$this->sectionLabel($report, $sectionId)} pada tugas pelaporan {$this->reportLabel($report)}",
                $meta
            );
        }

        return $result;
    }

    /**
     * @param  array{actor_username?: string|null}  $meta
     */
    private function actorLabel(array $meta): string
    {
        return $meta['actor_username'] ?? 'User';
    }

    private function reportLabel(Report $report): string
    {
        $report->loadMissing('reportType');

        $annexNumber = $report->reportType?->annex_number ?? 'Annex tidak diketahui';
        $reportTypeName = $report->reportType?->name ?? 'Jenis laporan tidak diketahui';

        return "{$report->product_name} (Batch {$report->batch_number}) - {$annexNumber} {$reportTypeName}";
    }

    private function sectionLabel(Report $report, string $sectionId): string
    {
        $report->loadMissing('reportType.sections');

        $section = $report->reportType?->sections
            ->first(fn ($row) => (string) $row->id === (string) $sectionId);

        if (! $section) {
            return "section ID {$sectionId}";
        }

        $sectionOrder = (int) ($section->order ?? 0);
        $measurementUnit = (string) ($section->measurement_unit ?? 'Section');

        return $sectionOrder > 0
            ? "section {$sectionOrder} ({$measurementUnit})"
            : "section {$measurementUnit}";
    }
}
