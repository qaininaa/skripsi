<?php

namespace Domain\Room\Dtos;

/**
 * DTO for creating a new room.
 */
class CreateRoomDto
{
    public function __construct(
        public readonly string $roomName,
        public readonly string $roomNumber,
        public readonly string $class,
    ) {}

    /**
     * Build DTO from validated request payload.
     *
     * @param  array{room_name: string, room_number: string, class: string}  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            roomName: (string) $validated['room_name'],
            roomNumber: (string) $validated['room_number'],
            class: (string) $validated['class'],
        );
    }

    /**
     * Convert DTO to persistence payload.
     *
     * @return array{room_name: string, room_number: string, class: string}
     */
    public function toArray(): array
    {
        return [
            'room_name' => $this->roomName,
            'room_number' => $this->roomNumber,
            'class' => $this->class,
        ];
    }
}
