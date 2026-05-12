<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\LoginDTO;
use App\Domains\Auth\Repositories\AuthRepository;
use App\Domains\User\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Service for authentication flow, rate limiting, and logout handling.
 */
class AuthService
{
    public function __construct(private AuthRepository $repository) {}

    /**
     * Authenticate user with login DTO and request context.
     *
     * @throws ValidationException
     */
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

    /**
     * Check whether user should be redirected to password change page.
     */
    public function shouldRedirectToPasswordChange(User $user): bool
    {
        return $user->mustChangePassword();
    }

    /**
     * Resolve dashboard route based on user role.
     */
    public function resolveDashboardRouteName(User $user): ?string
    {
        return match ($user->role) {
            'super' => 'dashboard.super-admin',
            'admin' => 'dashboard.admin-qc',
            'analis' => 'dashboard.analis',
            default => null,
        };
    }

    /**
     * Logout current user and invalidate session.
     */
    public function logout(Request $request): void
    {
        $this->repository->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Ensure login attempts do not exceed throttle limit.
     *
     * @throws ValidationException
     */
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

    /**
     * Build throttle key from username and request IP.
     */
    private function throttleKey(LoginDTO $dto, Request $request): string
    {
        return Str::transliterate(Str::lower($dto->username).'|'.$request->ip());
    }
}
