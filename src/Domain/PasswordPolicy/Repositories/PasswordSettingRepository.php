<?php

namespace Domain\PasswordPolicy\Repositories;

use Domain\PasswordPolicy\Interfaces\PasswordSettingRepositoryInterface;
use Domain\PasswordPolicy\Models\PasswordSetting;

/**
 * Eloquent implementation of PasswordSettingRepositoryInterface.
 */
class PasswordSettingRepository implements PasswordSettingRepositoryInterface
{
    public function getHistoryCount(): int
    {
        return (int) PasswordSetting::getValue('password_history_count', 3);
    }

    public function getExpirationDays(): int
    {
        return (int) PasswordSetting::getValue('password_expiration_days', 90);
    }

    public function updateSettings(int $passwordExpirationDays, int $passwordHistoryCount): void
    {
        PasswordSetting::setValue('password_expiration_days', (string) $passwordExpirationDays);
        PasswordSetting::setValue('password_history_count', (string) $passwordHistoryCount);
    }

    /**
     * @return array{password_expiration_days: int, password_history_count: int}
     */
    public function getSettings(): array
    {
        return [
            'password_expiration_days' => $this->getExpirationDays(),
            'password_history_count' => $this->getHistoryCount(),
        ];
    }
}
