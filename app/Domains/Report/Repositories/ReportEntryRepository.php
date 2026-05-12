<?php

namespace App\Domains\Report\Repositories;

use App\Domains\Report\Models\PersonnelInstance;
use App\Domains\Report\Models\PersonnelRow;
use App\Domains\Report\Models\PersonnelSamplingEntry;
use App\Domains\Report\Models\ReportEnvironmentalEntry;
use App\Domains\Report\Models\ReportSectionColumn;
use App\Domains\Report\Models\ReportSectionNote;

/**
 * Repository for ReportEntry persistence operations.
 */
class ReportEntryRepository
{
    /**
     * @return array<int, string>
     */
    public function getLockedEnvironmentalEntryKeys(string $reportId, string $currentUserId): array
    {
        return ReportEnvironmentalEntry::where('report_id', $reportId)
            ->where('analyst_id', '!=', $currentUserId)
            ->whereNotNull('analyst_id')
            ->where(fn ($q) => $q->whereNotNull('cfu_bacteria')->orWhereNotNull('cfu_fungi'))
            ->get()
            ->map(fn ($e) => "{$e->env_section_instance_id}-{$e->period_number}-{$e->shift}")
            ->toArray();
    }

    public function upsertEnvironmentalEntry(array $identity, array $payload): void
    {
        ReportEnvironmentalEntry::updateOrCreate($identity, $payload);
    }

    public function updateEnvironmentalEntryTimes(array $identity, ?string $startTime, ?string $endTime): void
    {
        ReportEnvironmentalEntry::where($identity)->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    public function upsertSectionColumn(
        string $reportId,
        string $sectionId,
        int $instanceNumber,
        int $periodNumber,
        ?string $label
    ): void {
        ReportSectionColumn::updateOrCreate(
            [
                'report_id' => $reportId,
                'section_id' => $sectionId,
                'instance_number' => $instanceNumber,
                'period_number' => $periodNumber,
            ],
            ['label' => $label]
        );
    }

    public function upsertSectionNote(
        string $reportId,
        string $sectionId,
        int $instanceNumber,
        ?string $notes,
        ?string $conclusion
    ): void {
        ReportSectionNote::updateOrCreate(
            [
                'report_id' => $reportId,
                'section_id' => $sectionId,
                'instance_number' => $instanceNumber,
            ],
            [
                'notes' => $notes,
                'conclusion' => $conclusion,
            ]
        );
    }

    public function firstOrCreatePersonnelInstance(string $reportId, string $methodId, int $pageNumber): PersonnelInstance
    {
        return PersonnelInstance::firstOrCreate([
            'report_id' => $reportId,
            'personnel_section_method_id' => $methodId,
            'page_number' => $pageNumber,
        ]);
    }

    public function findPersonnelInstance(string $reportId, string $instanceId): ?PersonnelInstance
    {
        return PersonnelInstance::where('id', $instanceId)
            ->where('report_id', $reportId)
            ->first();
    }

    public function firstOrNewPersonnelRow(string $personnelInstanceId, int $rowOrder): PersonnelRow
    {
        return PersonnelRow::firstOrNew([
            'personnel_instance_id' => $personnelInstanceId,
            'row_order' => $rowOrder,
        ]);
    }

    public function upsertPersonnelSamplingEntry(string $rowId, string $pointId, array $payload): void
    {
        PersonnelSamplingEntry::updateOrCreate(
            [
                'personnel_row_id' => $rowId,
                'sampling_point_id' => $pointId,
            ],
            $payload
        );
    }
}
