<?php

namespace Domain\ReportType\Dtos;

/**
 * DTO for creating a report section within a report type.
 */
class CreateReportSectionDto
{
    public function __construct(
        public readonly string $measurementUnit,
        public readonly string $measurementType,
        public readonly int $maxColumn,
        public readonly ?string $columnLabel,
        public readonly string $timeSlotType,
        public readonly bool $hasMachineSetup,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            measurementUnit: (string) $validated['measurement_unit'],
            measurementType: (string) $validated['measurement_type'],
            maxColumn: (int) $validated['max_column'],
            columnLabel: self::nullableString($validated['column_label'] ?? null),
            timeSlotType: (string) $validated['time_slot_type'],
            hasMachineSetup: (bool) ($validated['has_machine_setup'] ?? false),
        );
    }

    /**
     * Convert DTO to a partial persistence payload (without report_type_id and order).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'measurement_unit' => $this->measurementUnit,
            'measurement_type' => $this->measurementType,
            'max_column' => $this->maxColumn,
            'column_label' => $this->columnLabel,
            'time_slot_type' => $this->timeSlotType,
            'has_machine_setup' => $this->hasMachineSetup,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
