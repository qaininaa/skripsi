<?php

namespace Domain\User\Repositories;

use Domain\User\Dtos\GetUsersFilterDto;
use Domain\User\Interfaces\UserRepositoryInterface;
use Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of UserRepositoryInterface.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Retrieve paginated users with optional search and role filters.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getUsers(GetUsersFilterDto $data, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->when($data->search !== null, function ($query) use ($data) {
                $query->where(function ($subQuery) use ($data) {
                    $subQuery->where('name', 'like', '%' . $data->search . '%')
                        ->orWhere('username', 'like', '%' . $data->search . '%');
                });
            })
            ->when($data->role !== null, fn ($query) => $query->where('role', $data->role))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Persist a new user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update existing user attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    /**
     * Delete user record.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * Check whether a manager role exists, excluding optional user id.
     */
    public function isManagerTaken(?string $excludeUserId = null): bool
    {
        return User::where('role', 'manager')
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->exists();
    }
}
