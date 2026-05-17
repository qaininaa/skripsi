<?php

namespace Domain\User\Dtos;

/**
 * DTO for password change payload.
 */
class PasswordChangeDto
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: (string) $data['current_password'],
            newPassword: (string) $data['password'],
        );
    }
}
