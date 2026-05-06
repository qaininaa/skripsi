<?php

namespace App\Domains\User\Services;

use App\Domains\User\DTOs\UserDTO;
use App\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepository;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    private const DEFAULT_PASSWORD = 'ethica';

    public function __construct(private UserRepository $repository) {}

    public function paginateForManagement(?string $search, ?string $role): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $role, 10);
    }

    public function create(UserDTO $dto, array $meta): User
    {
        $payload = $dto->toCreatePayload();
        $payload['password'] = self::DEFAULT_PASSWORD;
        $payload['last_password_changed_at'] = null;

        $user = $this->repository->create($payload);

        $this->auditLog('create_user', "Membuat pengguna baru: {$user->name} ({$user->username})", $meta);

        return $user;
    }

    public function resetPassword(User $user, array $meta): User
    {
        $payload = [
            'password' => self::DEFAULT_PASSWORD,
            'last_password_changed_at' => null,
        ];

        $updatedUser = $this->repository->update($user, $payload);

        $this->auditLog('reset_user_password', "Reset password pengguna: {$updatedUser->name} ({$updatedUser->username}) ke default", $meta);

        return $updatedUser;
    }

    public function delete(User $user, array $meta): void
    {
        $info = "{$user->name} ({$user->username})";

        $this->repository->delete($user);

        $this->auditLog('delete_user', "Menghapus pengguna: {$info}", $meta);
    }

    public function isManajerTaken(?string $excludeUserId = null): bool
    {
        return $this->repository->isManajerTaken($excludeUserId);
    }

    private function auditLog(string $action, string $description, array $meta): void
    {
        AuditLog::create([
            'user_id' => $meta['user_id'] ?? null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $meta['ip_address'] ?? null,
            'user_agent' => $meta['user_agent'] ?? null,
        ]);
    }
}
