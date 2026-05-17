<?php

namespace Domain\Report\Dtos;

/**
 * DTO for analyst report drafting save action payload.
 */
class ReportDraftingSaveDto
{
    /**
     * @param string $action
     * @param string|null $supervisorId
     * @param array<string, mixed> $entries
     * @param array<string, mixed> $payload Full request payload for downstream services
     */
    public function __construct(
        public string $action,
        public ?string $supervisorId,
        public array $entries,
        public array $payload,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $rawPayload
     */
    public static function fromArray(array $validated, array $rawPayload = []): self
    {
        return new self(
            action: (string) ($validated['action'] ?? 'save'),
            supervisorId: $validated['supervisor_id'] ?? null,
            entries: (array) ($validated['entries'] ?? []),
            payload: $rawPayload,
        );
    }
}
