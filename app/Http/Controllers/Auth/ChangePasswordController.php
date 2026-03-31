<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordHistory;
use App\Models\SystemSetting;
use App\Rules\PasswordComplexity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', new PasswordComplexity],
        ]);

        $user = $request->user();
        $newPassword = $request->input('password');

        // Check password history
        $historyCount = (int) SystemSetting::getValue('password_history_count', 3);
        $recentPasswords = $user->passwordHistories()->take($historyCount)->get();

        foreach ($recentPasswords as $history) {
            if (Hash::check($newPassword, $history->password)) {
                return back()->withErrors([
                    'password' => "Password tidak boleh sama dengan {$historyCount} password terakhir.",
                ]);
            }
        }

        // Also check current password
        if (Hash::check($newPassword, $user->password)) {
            return back()->withErrors([
                'password' => 'Password baru tidak boleh sama dengan password saat ini.',
            ]);
        }

        // Save old password to history
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $user->password,
            'created_at' => now(),
        ]);

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
            'last_password_changed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Password berhasil diubah.');
    }
}
