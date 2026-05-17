<?php

namespace Domain\User\Repositories;

use Domain\User\Dtos\LoginDto;
use Domain\User\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Authentication repository.
 */
class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Attempt to authenticate a user with given credentials.
     */
    public function attempt(LoginDto $dto): bool
    {
        return Auth::attempt($dto->credentials(), $dto->remember);
    }

    /**
     * Logout current web-guard user.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
