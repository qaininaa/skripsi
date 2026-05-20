<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\ReportDashboardRepositoryInterface;
use Domain\Report\Models\ReportSectionNote;
use Illuminate\Support\Collection;

class ReportDashboardRepository implements ReportDashboardRepositoryInterface
{
    /**
     * @return Collection<int, object>
     */
    public function getApprovedManagerTmsSections(string $managerId): Collection
    {
        return ReportSectionNote::query()
            ->join('reports', 'report_section_notes.report_id', '=', 'reports.id')
            ->join('sections', 'report_section_notes.section_id', '=', 'sections.id')
            ->join('report_approvals', function ($join) use ($managerId): void {
                $join->on('report_approvals.report_id', '=', 'reports.id')
                    ->where('report_approvals.step', 3)
                    ->where('report_approvals.status', 'approved')
                    ->where('report_approvals.user_id', $managerId);
            })
            ->where('report_section_notes.conclusion', 'TMS')
            ->select([
                'reports.id as report_id',
                'reports.product_name',
                'reports.batch_number',
                'reports.created_at as report_created_at',
                'sections.id as section_id',
                'sections.order as section_order',
                'sections.measurement_type',
                'sections.measurement_unit',
                'report_section_notes.instance_number',
                'report_approvals.signed_at as approved_at',
            ])
            ->orderByDesc('report_approvals.signed_at')
            ->orderByDesc('reports.created_at')
            ->orderBy('sections.order')
            ->orderBy('report_section_notes.instance_number')
            ->get();
    }
}

