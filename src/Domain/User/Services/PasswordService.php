<?php

namespace Domain\User\Services;

use Domain\PasswordPolicy\Services\PasswordPolicyService;
use Domain\User\Dtos\PasswordChangeDto;
use Domain\User\Interfaces\PasswordRepositoryInterface;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Service for user password change with history reuse rules.
 */
class PasswordService
{
    public function __construct(
        private PasswordRepositoryInterface $repository,
        private PasswordPolicyService $passwordPolicyService,
    ) {}

    /**
     * Whether super-admin user can bypass password change requirement.
     */
    public function shouldBypassForSuper(User $user): bool
    {
        return $user->role === 'super';
    }

    /**
     * Change user password while enforcing reuse history rules.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, PasswordChangeDto $dto): void
    {
        $historyCount = $this->passwordPolicyService->getHistoryCount();
        $newPassword = $dto->newPassword;

        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password baru tidak boleh sama dengan password saat ini.',
            ]);
        }

        $recentPasswords = $this->repository->recentHistories($user, $historyCount);
        foreach ($recentPasswords as $history) {
            if (Hash::check($newPassword, $history->password)) {
                throw ValidationException::withMessages([
                    'password' => "Password tidak boleh sama dengan {$historyCount} password terakhir.",
                ]);
            }
        }

        $this->repository->addHistory($user, $user->password);
        $this->repository->pruneHistories($user, $historyCount);
        $this->repository->updateUserPassword($user, Hash::make($newPassword));
    }

    /**
     * Reset user password as administrator.
     *
     * Archives the user's current password into history so they cannot
     * reuse it during the forced change-password flow, and clears
     * last_password_changed_at to require an immediate change on next login.
     */
    public function resetByAdmin(User $user, string $newPlainPassword): void
    {
        $historyCount = $this->passwordPolicyService->getHistoryCount();

        // Archive the current password before overwriting so it counts toward history.
        if (! empty($user->password)) {
            $this->repository->addHistory($user, $user->password);
            $this->repository->pruneHistories($user, $historyCount);
        }

        $user->password = Hash::make($newPlainPassword);
        $user->last_password_changed_at = null;
        $user->save();
    }
}
