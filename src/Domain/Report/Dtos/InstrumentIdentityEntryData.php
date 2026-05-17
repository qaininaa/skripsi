<?php

namespace Domain\Report\Dtos;

/**
 * DTO for instrument identity payload.
 */
class InstrumentIdentityEntryData
{
    public function __construct(
        public string $toolName,
        public ?string $noId,
        public ?string $calibrationDate,
        public ?string $dueDate,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $toolName = trim((string) ($payload['tool_name'] ?? 'Air Sampler'));

        return new self(
            toolName: $toolName !== '' ? $toolName : 'Air Sampler',
            noId: self::nullableString($payload['no_id'] ?? null),
            calibrationDate: self::nullableString($payload['calibration_date'] ?? null),
            dueDate: self::nullableString($payload['due_date'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
