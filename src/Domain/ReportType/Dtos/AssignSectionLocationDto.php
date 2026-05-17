<?php

namespace Domain\ReportType\Dtos;

/**
 * DTO carrying location assignment payload for a report section.
 */
class AssignSectionLocationDto
{
    public function __construct(
        public readonly string $locationId,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            locationId: (string) $validated['location_id'],
        );
    }
}
