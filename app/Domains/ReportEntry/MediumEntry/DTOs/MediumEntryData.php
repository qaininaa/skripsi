<?php

namespace App\Domains\ReportEntry\MediumEntry\DTOs;

/**
 * DTO for medium identity payload.
 */
class MediumEntryData
{
    public function __construct(
        public string $name,
        public ?string $batchNumber,
        public ?string $gptNumber,
        public ?string $expirationDate,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(string $name, array $payload): self
    {
        $mediumName = trim($name);

        return new self(
            name: $mediumName,
            batchNumber: self::nullableString($payload['batch_number'] ?? null),
            gptNumber: self::nullableString($payload['gpt_number'] ?? null),
            expirationDate: self::nullableString($payload['expiration_date'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
