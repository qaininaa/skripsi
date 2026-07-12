<?php

namespace Domain\User\Repositories;

use Domain\User\Interfaces\PasswordRepositoryInterface;
use Domain\User\Models\PasswordHistory;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

/**
 * Repository for password history persistence per user.
 */
class PasswordRepository implements PasswordRepositoryInterface
{
    /**
     * Fetch most recent password history entries up to limit.
     *
     * @return Collection<int, PasswordHistory>
     */
    public function recentHistories(User $user, int $limit): Collection
    {
        return $user->passwordHistories()->take($limit)->get();
    }

    /**
     * Append hashed password to user's password history.
     */
    public function addHistory(User $user, string $hashedPassword): void
    {
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $hashedPassword,
            'created_at' => now(),
        ]);
    }

    /**
     * Remove password history entries older than the limit.
     */
    public function pruneHistories(User $user, int $limit): void
    {
        $keepIds = $user->passwordHistories()->take($limit)->pluck('id');
        $user->passwordHistories()->whereNotIn('id', $keepIds)->delete();
    }

    /**
     * Update user password and last changed timestamp.
     */
    public function updateUserPassword(User $user, string $hashedPassword): void
    {
        $user->update([
            'password' => $hashedPassword,
            'last_password_changed_at' => now(),
        ]);
    }
}
