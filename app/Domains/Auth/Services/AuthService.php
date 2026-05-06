<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\LoginDTO;
use App\Domains\Auth\Repositories\AuthRepository;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private AuthRepository $repository) {}

    public function authenticate(LoginDTO $dto, Request $request): void
    {
        $this->ensureIsNotRateLimited($dto, $request);

        if (! $this->repository->attempt($dto)) {
            RateLimiter::hit($this->throttleKey($dto, $request));

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($dto, $request));
    }

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
        $this->repository->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function ensureIsNotRateLimited(LoginDTO $dto, Request $request): void
    {
        $throttleKey = $this->throttleKey($dto, $request);

        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(LoginDTO $dto, Request $request): string
    {
        return Str::transliterate(Str::lower($dto->username) . '|' . $request->ip());
    }
}
