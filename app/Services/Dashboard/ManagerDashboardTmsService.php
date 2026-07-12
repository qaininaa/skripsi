<?php

namespace App\Services\Dashboard;

use Domain\Report\Interfaces\ReportDashboardRepositoryInterface;
use Domain\ReportType\Models\ReportSection;
use Illuminate\Support\Carbon;

class ManagerDashboardTmsService
{
    private const METHOD_LABELS = [
        'air_sampler' => 'Air Sampler',
        'settle_plate' => 'Settle Plate',
        'contact_plate' => 'Contact Plate',
        'swab' => 'Swab',
    ];

    public function __construct(
        private ReportDashboardRepositoryInterface $repository,
    ) {}

    /**
     * Build manager dashboard TMS summary from approved reports.
     *
     * @return array{
     *     managerTmsTotalMethods:int,
     *     managerTmsTotalReports:int,
     *     managerTmsByMethod:array<int, array{
     *         key:string,
     *         label:string,
     *         count:int,
     *         items:array<int, array{
     *             report_id:string,
     *             product_name:string,
     *             batch_number:?string,
     *             section_id:string,
     *             section_label:string,
     *             section_type:string,
     *             section_order:int,
     *             instance_number:int,
     *             approved_at:?Carbon
     *         }>
     *     }>
     * }
     */
    public function buildSummaryForManager(string $managerId): array
    {
        $rows = $this->repository->getApprovedManagerTmsSections($managerId);

        $methodBuckets = [];
        foreach (self::METHOD_LABELS as $methodKey => $methodLabel) {
            $methodBuckets[$methodKey] = [
                'key' => $methodKey,
                'label' => $methodLabel,
                'count' => 0,
                'items' => [],
            ];
        }

        $reportIds = [];

        foreach ($rows as $row) {
            $methodKey = ReportSection::normalizeMeasurementType((string) $row->measurement_type);
            $methodLabel = $this->resolveMethodLabel($methodKey, (string) $row->measurement_type);

            if (! array_key_exists($methodKey, $methodBuckets)) {
                $methodBuckets[$methodKey] = [
                    'key' => $methodKey,
                    'label' => $methodLabel,
                    'count' => 0,
                    'items' => [],
                ];
            }

            $methodBuckets[$methodKey]['count']++;
            $methodBuckets[$methodKey]['items'][] = [
                'report_id' => (string) $row->report_id,
                'product_name' => (string) $row->product_name,
                'batch_number' => $row->batch_number !== null ? (string) $row->batch_number : null,
                'section_id' => (string) $row->section_id,
                'section_label' => $this->resolveSectionLabel(
                    measurementUnit: (string) ($row->measurement_unit ?? ''),
                    sectionOrder: (int) ($row->section_order ?? 0)
                ),
                'section_type' => $methodLabel,
                'section_order' => (int) ($row->section_order ?? 0),
                'instance_number' => max(1, (int) ($row->instance_number ?? 1)),
                'approved_at' => $row->approved_at ? Carbon::parse($row->approved_at) : null,
            ];

            $reportIds[(string) $row->report_id] = true;
        }

        $sortedBuckets = collect($methodBuckets)
            ->filter(fn (array $bucket) => (int) ($bucket['count'] ?? 0) > 0)
            ->sortByDesc('count')
            ->values()
            ->all();

        return [
            'managerTmsTotalMethods' => (int) collect($sortedBuckets)->sum('count'),
            'managerTmsTotalReports' => count($reportIds),
            'managerTmsByMethod' => $sortedBuckets,
        ];
    }

    private function resolveMethodLabel(string $methodKey, string $measurementType): string
    {
        if (array_key_exists($methodKey, self::METHOD_LABELS)) {
            return self::METHOD_LABELS[$methodKey];
        }

        $fallback = trim($measurementType);
        if ($fallback !== '') {
            return ucwords(strtolower($fallback));
        }

        return ucwords(str_replace('_', ' ', $methodKey));
    }

    private function resolveSectionLabel(string $measurementUnit, int $sectionOrder): string
    {
        $label = trim($measurementUnit);
        if ($label !== '') {
            return $label;
        }

        return $sectionOrder > 0 ? 'Section ' . $sectionOrder : 'Section';
    }
}
