<?php

namespace Domain\Location\Dtos;

/**
 * DTO for creating a new location.
 */
class CreateLocationDto
{
    public function __construct(
        public readonly string $roomId,
        public readonly string $frequency,
        public readonly string $locationNumber,
        public readonly string $measurementType,
        public readonly ?int $alertLimitTotal,
        public readonly ?int $alertLimitFungi,
        public readonly ?int $alertActionTotal,
        public readonly ?int $alertActionFungi,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            roomId: (string) $validated['room_id'],
            frequency: (string) $validated['frequency'],
            locationNumber: (string) $validated['location_number'],
            measurementType: (string) $validated['measurement_type'],
            alertLimitTotal: isset($validated['alert_limit_total']) ? (int) $validated['alert_limit_total'] : null,
            alertLimitFungi: isset($validated['alert_limit_fungi']) ? (int) $validated['alert_limit_fungi'] : null,
            alertActionTotal: isset($validated['alert_action_total']) ? (int) $validated['alert_action_total'] : null,
            alertActionFungi: isset($validated['alert_action_fungi']) ? (int) $validated['alert_action_fungi'] : null,
        );
    }

    /**
     * Convert DTO to persistence payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'room_id' => $this->roomId,
            'frequency' => $this->frequency,
            'location_number' => $this->locationNumber,
            'measurement_type' => $this->measurementType,
            'alert_limit_total' => $this->alertLimitTotal,
            'alert_limit_fungi' => $this->alertLimitFungi,
            'alert_action_total' => $this->alertActionTotal,
            'alert_action_fungi' => $this->alertActionFungi,
        ];
    }
}
