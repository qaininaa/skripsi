<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\ReportEntryRepositoryInterface;
use Domain\Report\Models\ReportEnvironmentalEntry;
use Domain\Report\Models\ReportSectionColumn;
use Domain\Report\Models\ReportSectionNote;

/**
 * Eloquent implementation of ReportEntryRepositoryInterface.
 */
class ReportEntryRepository implements ReportEntryRepositoryInterface
{
    /**
     * Return entry keys whose CFU is already filled by another analyst.
     * Key format: "{env_section_instance_id}-{period_number}-{shift}"
     *
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

    /**
     * Return entry keys whose start_time/end_time is already filled by another analyst.
     * Key format: "{env_section_instance_id}-{period_number}-{shift}"
     *
     * @return array<int, string>
     */
    public function getLockedTimeEntryKeys(string $reportId, string $currentUserId): array
    {
        return ReportEnvironmentalEntry::where('report_id', $reportId)
            ->where('analyst_id', '!=', $currentUserId)
            ->whereNotNull('analyst_id')
            ->where(fn ($q) => $q->whereNotNull('start_time')->orWhereNotNull('end_time'))
            ->get()
            ->map(fn ($e) => "{$e->env_section_instance_id}-{$e->period_number}-{$e->shift}")
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $payload
     */
    public function upsertEnvironmentalEntry(array $identity, array $payload): void
    {
        ReportEnvironmentalEntry::updateOrCreate($identity, $payload);
    }

    /**
     * @param  array<string, mixed>  $identity
     */
    public function updateEnvironmentalEntryTimes(array $identity, ?string $startTime, ?string $endTime): void
    {
        ReportEnvironmentalEntry::where($identity)->update([
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);
    }

    /**
     * @param  array<string, mixed>  $identity
     */
    public function findEnvironmentalEntry(array $identity): ?ReportEnvironmentalEntry
    {
        return ReportEnvironmentalEntry::query()
            ->where($identity)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $payload
     */
    public function createEnvironmentalEntry(array $identity, array $payload): ReportEnvironmentalEntry
    {
        return ReportEnvironmentalEntry::query()->create(array_merge($identity, $payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateEnvironmentalEntry(ReportEnvironmentalEntry $entry, array $payload): void
    {
        $entry->update($payload);
    }

    public function findSectionColumn(
        string $reportId,
        string $sectionId,
        int $instanceNumber,
        int $periodNumber
    ): ?object {
        return ReportSectionColumn::where([
            'report_id'       => $reportId,
            'section_id'      => $sectionId,
            'instance_number' => $instanceNumber,
            'period_number'   => $periodNumber,
        ])->first();
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
                'report_id'       => $reportId,
                'section_id'      => $sectionId,
                'instance_number' => $instanceNumber,
                'period_number'   => $periodNumber,
            ],
            ['label' => $label]
        );
    }

    public function findSectionNote(
        string $reportId,
        string $sectionId,
        int $instanceNumber
    ): ?object {
        return ReportSectionNote::where([
            'report_id' => $reportId,
            'section_id' => $sectionId,
            'instance_number' => $instanceNumber,
        ])->first();
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
                'report_id'       => $reportId,
                'section_id'      => $sectionId,
                'instance_number' => $instanceNumber,
            ],
            [
                'notes'      => $notes,
                'conclusion' => $conclusion,
            ]
        );
    }
}
