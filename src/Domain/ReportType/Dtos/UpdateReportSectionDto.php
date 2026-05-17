<?php

namespace Domain\ReportType\Dtos;

/**
 * DTO for updating a report section.
 */
class UpdateReportSectionDto
{
    public function __construct(
        public readonly string $measurementUnit,
        public readonly string $measurementType,
        public readonly int $maxColumn,
        public readonly string $columnLabel,
        public readonly string $timeSlotType,
        public readonly bool $hasMachineSetup,
        public readonly int $order,
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
            columnLabel: (string) $validated['column_label'],
            timeSlotType: (string) $validated['time_slot_type'],
            hasMachineSetup: (bool) ($validated['has_machine_setup'] ?? false),
            order: (int) $validated['order'],
        );
    }

    /**
     * Convert DTO to a persistence payload.
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
            'order' => $this->order,
        ];
    }
}
