<?php

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\DTOs\LoginDTO;
use Illuminate\Support\Facades\Auth;

class AuthRepository
{
    /**
     * Attempt authentication using login cred     */
    public function attempt(LoginDTO $dto): bool
    {
        return Auth::attempt($dto->credentials(), $dto->remember);
    }

    /**
     * Logout current authenticated web user.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
