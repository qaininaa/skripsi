<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\PasswordChangeDTO;
use App\Domains\Auth\Repositories\PasswordRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Service for password update policy and password history checks.
 */
class PasswordService
{
    public function __construct(private PasswordRepository $repository) {}

    /**
     * Determine whether user can bypass password expiration check.
     */
    public function shouldBypassForSuper(User $user): bool
    {
        return $user->role === 'super';
    }

    /**
     * Change user password while enforcing history and reuse rules.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, PasswordChangeDTO $dto): void
    {
        $historyCount = $this->repository->getHistoryCount();
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
