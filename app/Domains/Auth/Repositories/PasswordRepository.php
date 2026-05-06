<?php

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Models\PasswordHistory;
use App\Domains\Auth\Models\PasswordSetting;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Repository for password history and password setting persistence.
 */
class PasswordRepository
{
    /**
     * Get configured password history limit.
     */
    public function getHistoryCount(): int
    {
        return (int) PasswordSetting::getValue('password_history_count');
    }

    /**
     * Fetch recent password histories for a user.
     *
     * @return Collection<int, PasswordHistory>
     */
    public function recentHistories(User $user, int $limit): Collection
    {
        return $user->passwordHistories()->take($limit)->get();
    }

    /**
     * Store password hash into password history.
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
     * Keep only recent password histories and remove older entries.
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
