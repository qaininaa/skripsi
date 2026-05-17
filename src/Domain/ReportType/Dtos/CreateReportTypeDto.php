<?php

namespace Domain\ReportType\Dtos;

/**
 * DTO for creating a report type with its medium and incubator configurations.
 */
class CreateReportTypeDto
{
    /**
     * @param  array<int, string>  $mediumLabels
     * @param  array<int, IncubatorTypeDto>  $incubators
     */
    public function __construct(
        public readonly string $sopCode,
        public readonly string $sopVersion,
        public readonly string $name,
        public readonly int $annexNumber,
        public readonly array $mediumLabels,
        public readonly array $incubators,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            sopCode: (string) $validated['sop_code'],
            sopVersion: (string) $validated['sop_version'],
            name: (string) $validated['name'],
            annexNumber: (int) $validated['annex_number'],
            mediumLabels: self::normalizeMediumLabels($validated['medium_labels'] ?? []),
            incubators: self::normalizeIncubators(
                $validated['incubator_labels'] ?? [],
                $validated['incubator_min_days'] ?? [],
            ),
        );
    }

    /**
     * Convert DTO to base persistence payload (without medium/incubator).
     *
     * @return array{sop_code: string, sop_version: string, name: string, annex_number: int}
     */
    public function toBasePayload(): array
    {
        return [
            'sop_code' => $this->sopCode,
            'sop_version' => $this->sopVersion,
            'name' => $this->name,
            'annex_number' => $this->annexNumber,
        ];
    }

    /**
     * Filter and trim medium labels.
     *
     * @param  array<int, mixed>  $labels
     * @return array<int, string>
     */
    private static function normalizeMediumLabels(array $labels): array
    {
        $normalized = [];
        foreach ($labels as $label) {
            $clean = trim((string) $label);
            if ($clean !== '') {
                $normalized[] = $clean;
            }
        }

        return $normalized;
    }

    /**
     * Pair incubator labels with minimum days, dropping empty labels.
     *
     * @param  array<int, mixed>  $labels
     * @param  array<int, mixed>  $minDays
     * @return array<int, IncubatorTypeDto>
     */
    private static function normalizeIncubators(array $labels, array $minDays): array
    {
        $incubators = [];
        foreach ($labels as $index => $label) {
            $clean = trim((string) $label);
            if ($clean === '') {
                continue;
            }

            $incubators[] = new IncubatorTypeDto(
                label: $clean,
                minDay: (int) ($minDays[$index] ?? 3),
            );
        }

        return $incubators;
    }
}
