<?php

namespace Domain\Report\Dtos;

/**
 * DTO for analyst report listing filter.
 */
class ReportClaimingFilterDto
{
    public function __construct(public string $status) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self((string) ($validated['status'] ?? 'all'));
    }
}
