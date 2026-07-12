<?php

namespace Domain\PasswordPolicy\Services;

use Domain\PasswordPolicy\Dtos\PasswordSettingDto;
use Domain\PasswordPolicy\Interfaces\PasswordSettingRepositoryInterface;

/**
 * Service for password policy configuration management.
 */
class PasswordPolicyService
{
    public function __construct(private PasswordSettingRepositoryInterface $repository) {}

    /**
     * Read current password policy settings.
     *
     * @return array{password_expiration_days: int, password_history_count: int}
     */
    public function getSettings(): array
    {
        return $this->repository->getSettings();
    }

    /**
     * Update password policy settings.
     */
    public function updateSettings(PasswordSettingDto $dto): void
    {
        $this->repository->updateSettings(
            $dto->passwordExpirationDays,
            $dto->passwordHistoryCount,
        );
    }

    /**
     * Get configured password history count (used by ChangePasswordService).
     */
    public function getHistoryCount(): int
    {
        return $this->repository->getHistoryCount();
    }

    /**
     * Get configured password expiration days (used by User model).
     */
    public function getExpirationDays(): int
    {
        return $this->repository->getExpirationDays();
    }
}
