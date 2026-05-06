<?php

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Models\PasswordHistory;
use App\Domains\Auth\Models\PasswordSetting;
use App\Models\User;
use Illuminate\Support\Collection;

class PasswordRepository
{
    public function getHistoryCount(): int
    {
        return (int) PasswordSetting::getValue('password_history_count', 3);
    }

    public function recentHistories(User $user, int $limit): Collection
    {
        return $user->passwordHistories()->take($limit)->get();
    }

    public function addHistory(User $user, string $hashedPassword): void
    {
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $hashedPassword,
            'created_at' => now(),
        ]);
    }

    public function pruneHistories(User $user, int $limit): void
    {
        $keepIds = $user->passwordHistories()->take($limit)->pluck('id');
        $user->passwordHistories()->whereNotIn('id', $keepIds)->delete();
    }

    public function updateUserPassword(User $user, string $hashedPassword): void
    {
        $user->update([
            'password' => $hashedPassword,
            'last_password_changed_at' => now(),
        ]);
    }
}
