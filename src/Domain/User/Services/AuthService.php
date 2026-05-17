<?php

namespace Domain\User\Services;

use Domain\User\Dtos\LoginDto;
use Domain\User\Interfaces\AuthRepositoryInterface;
use Domain\User\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Service for authentication flow with rate limiting.
 */
class AuthService
{
    public function __construct(private AuthRepositoryInterface $repository) {}

    /**
     * Authenticate using login DTO and request context (for IP-based throttle).
     *
     * @throws ValidationException
     */
    public function authenticate(LoginDto $dto, Request $request): void
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
     * Whether user must change their password before continuing.
     */
    public function shouldRedirectToPasswordChange(User $user): bool
    {
        return $user->mustChangePassword();
    }

    /**
     * Resolve dashboard route name based on user role.
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
     * Logout user and invalidate session.
     */
    public function logout(Request $request): void
    {
        $this->repository->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(LoginDto $dto, Request $request): void
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

    private function throttleKey(LoginDto $dto, Request $request): string
    {
        return Str::transliterate(Str::lower($dto->username) . '|' . $request->ip());
    }
}
