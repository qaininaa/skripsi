<?php

namespace Domain\User\Interfaces;

use Domain\User\Dtos\CreateUserDto;
use Domain\User\Dtos\GetUsersFilterDto;
use Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for user data access.
 */
interface UserRepositoryInterface
{
    /**
     * Retrieve a paginated, filtered list of users.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getUsers(GetUsersFilterDto $data, int $perPage = 10): LengthAwarePaginator;

    /**
     * Persist a new user with given attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * Update an existing user with given attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    /**
     * Delete a user record.
     */
    public function delete(User $user): void;

    /**
     * Check whether a manager role is already taken.
     */
    public function isManagerTaken(?string $excludeUserId = null): bool;
}
