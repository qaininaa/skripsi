<?php

namespace Domain\PasswordPolicy\Interfaces;

/**
 * Contract for password policy settings persistence.
 */
interface PasswordSettingRepositoryInterface
{
    /**
     * Get configured password history count.
     */
    public function getHistoryCount(): int;

    /**
     * Get configured password expiration days.
     */
    public function getExpirationDays(): int;

    /**
     * Update password policy values (expiration days, history count).
     */
    public function updateSettings(int $passwordExpirationDays, int $passwordHistoryCount): void;

    /**
     * Get current password policy settings as array.
     *
     * @return array{password_expiration_days: int, password_history_count: int}
     */
    public function getSettings(): array;
}
