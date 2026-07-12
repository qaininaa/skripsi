<?php

namespace Domain\PasswordPolicy\Dtos;

/**
 * DTO for password policy settings.
 */
class PasswordSettingDto
{
    public function __construct(
        public readonly int $passwordExpirationDays,
        public readonly int $passwordHistoryCount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            passwordExpirationDays: (int) $data['password_expiration_days'],
            passwordHistoryCount: (int) $data['password_history_count'],
        );
    }
}
