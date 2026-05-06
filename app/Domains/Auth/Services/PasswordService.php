<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\PasswordChangeDTO;
use App\Domains\Auth\Repositories\PasswordRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordService
{
    public function __construct(private PasswordRepository $repository) {}

    public function shouldBypassForSuper(User $user): bool
    {
        return $user->role === 'super';
    }

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
