<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
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

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function isManajerTaken(?string $excludeUserId = null): bool
    {
        return User::where('role', 'manajer')
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->exists();
    }
}
