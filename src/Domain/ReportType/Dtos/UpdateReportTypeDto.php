<?php

namespace Domain\ReportType\Dtos;

/**
 * DTO for updating a report type with its medium and incubator configurations.
 */
class UpdateReportTypeDto
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
        $createDto = CreateReportTypeDto::fromArray($validated);

        return new self(
            sopCode: $createDto->sopCode,
            sopVersion: $createDto->sopVersion,
            name: $createDto->name,
            annexNumber: $createDto->annexNumber,
            mediumLabels: $createDto->mediumLabels,
            incubators: $createDto->incubators,
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
}
