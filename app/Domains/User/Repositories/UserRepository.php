<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Repository for user persistence and management queries.
 */
class UserRepository
{
    /**
     * Get paginated users for management page with optional filters.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateForManagement(?string $search, ?string $role, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            }))
            ->when($role, fn ($q) => $q->where('role', $role))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update an existing user record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    /**
     * Delete a user record.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * Check whether a manager role already exists.
     */
    public function isManagerTaken(?string $excludeUserId = null): bool
    {
        return User::where('role', 'manajer')
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->exists();
    }
}
