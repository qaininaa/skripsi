<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\PasswordHistory;
use App\Domains\Auth\Models\PasswordSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordService
{
    public function shouldBypassForSuper(User $user): bool
    {
        return $user->role === 'super';
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $historyCount = $this->historyCount();

        $recentPasswords = $user->passwordHistories()->take($historyCount)->get();
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

        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $user->password,
            'created_at' => now(),
        ]);

        $keepIds = $user->passwordHistories()->take($historyCount)->pluck('id');
        $user->passwordHistories()->whereNotIn('id', $keepIds)->delete();

        $user->update([
            'password' => Hash::make($newPassword),
            'last_password_changed_at' => now(),
        ]);
    }

    private function historyCount(): int
    {
        return (int) PasswordSetting::getValue('password_history_count', 3);
    }
}
