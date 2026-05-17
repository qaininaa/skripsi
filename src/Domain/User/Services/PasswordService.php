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

        $recentPasswords = $this->repository->recentHistories($user, $historyCount);
        foreach ($recentPasswords as $history) {
            if (Hash::check($newPassword, $history->password)) {
                throw ValidationException::withMessages([
                    'password' => "Password tidak boleh sama dengan {$historyCount} password terakhir.",
                ]);
            }
        }

        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password baru tidak boleh sama dengan password saat ini.',
            ]);
        }

        $this->repository->addHistory($user, $user->password);
        $this->repository->pruneHistories($user, $historyCount);
        $this->repository->updateUserPassword($user, Hash::make($newPassword));
    }
}
