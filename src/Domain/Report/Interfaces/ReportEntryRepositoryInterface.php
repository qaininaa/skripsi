<?php

namespace Domain\Report\Interfaces;

/**
 * Contract for ReportEntry persistence operations.
 */
interface ReportEntryRepositoryInterface
{
    /**
     * @return array<int, string>
     */
    public function getLockedEnvironmentalEntryKeys(string $reportId, string $currentUserId): array;

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $payload
     */
    public function upsertEnvironmentalEntry(array $identity, array $payload): void;

    /**
     * @param  array<string, mixed>  $identity
     */
    public function updateEnvironmentalEntryTimes(array $identity, ?string $startTime, ?string $endTime): void;

    public function upsertSectionColumn(
        string $reportId,
        string $sectionId,
        int $instanceNumber,
        int $periodNumber,
        ?string $label
    ): void;

    public function upsertSectionNote(
        string $reportId,
        string $sectionId,
        int $instanceNumber,
        ?string $notes,
        ?string $conclusion
    ): void;
}
