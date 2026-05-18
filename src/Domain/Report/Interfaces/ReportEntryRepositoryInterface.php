<?php

namespace Domain\Report\Interfaces;

use Domain\Report\Models\ReportEnvironmentalEntry;

/**
 * Contract for ReportEntry persistence operations.
 */
interface ReportEntryRepositoryInterface
{
    /**
     * Return entry keys whose CFU is already filled by another analyst.
     * Key format: "{env_section_instance_id}-{period_number}-{shift}"
     *
     * @return array<int, string>
     */
    public function getLockedEnvironmentalEntryKeys(string $reportId, string $currentUserId): array;

    /**
     * Return entry keys whose start_time or end_time is already filled by another analyst.
     * Key format: "{env_section_instance_id}-{period_number}-{shift}"
     *
     * @return array<int, string>
     */
    public function getLockedTimeEntryKeys(string $reportId, string $currentUserId): array;

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $payload
     */
    public function upsertEnvironmentalEntry(array $identity, array $payload): void;

    /**
     * @param  array<string, mixed>  $identity
     */
    public function updateEnvironmentalEntryTimes(array $identity, ?string $startTime, ?string $endTime): void;

    /**
     * Find one environmental entry by identity keys.
     *
     * @param  array<string, mixed>  $identity
     */
    public function findEnvironmentalEntry(array $identity): ?ReportEnvironmentalEntry;

    /**
     * Create one environmental entry with identity + payload.
     *
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $payload
     */
    public function createEnvironmentalEntry(array $identity, array $payload): ReportEnvironmentalEntry;

    /**
     * Update existing environmental entry fields.
     *
     * @param  array<string, mixed>  $payload
     */
    public function updateEnvironmentalEntry(ReportEnvironmentalEntry $entry, array $payload): void;

    /**
     * Find a ReportSectionColumn row for the given identity.
     * Returns null if not found.
     */
    public function findSectionColumn(
        string $reportId,
        string $sectionId,
        int $instanceNumber,
        int $periodNumber
    ): ?object;

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
