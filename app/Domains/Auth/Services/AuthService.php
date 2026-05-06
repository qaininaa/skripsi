<?php

namespace App\Domains\Auth\Services;

use App\Domains\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function shouldRedirectToPasswordChange(User $user): bool
    {
        return $user->mustChangePassword();
    }

    public function resolveDashboardRouteName(User $user): ?string
    {
        return match ($user->role) {
            'super' => 'dashboard.super-admin',
            'admin' => 'dashboard.admin-qc',
            'analis' => 'dashboard.analis',
            default => null,
        };
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
