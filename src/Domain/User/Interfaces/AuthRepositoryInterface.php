<?php

namespace Domain\User\Interfaces;

use Domain\User\Dtos\LoginDto;

/**
 * Contract for authentication operations.
 */
interface AuthRepositoryInterface
{
    /**
     * Attempt authentication using login credentials.
     */
    public function attempt(LoginDto $dto): bool;

    /**
     * Logout current authenticated web user.
     */
    public function logout(): void;
}
