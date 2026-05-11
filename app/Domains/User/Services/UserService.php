<?php

namespace App\Domains\User\Services;

use App\Domains\User\DTOs\UserDTO;
use App\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepository;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(private UserRepository $repository) {}

    /**
     * Get paginated users for management page.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateForManagement(?string $search, ?string $role): LengthAwarePaginator
    {
        return $this->repository->paginateForManagement($search, $role, 10);
    }

    /**
     * Create new user with default password and audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function create(UserDTO $dto, array $meta): User
    {
        $payload = $dto->toCreatePayload();
        $payload['password'] = $this->defaultPassword();
        $payload['last_password_changed_at'] = null;

        $user = $this->repository->create($payload);

        $this->auditLog('create_user', "Membuat pengguna baru: {$user->name} ({$user->username})", $meta);

        return $user;
    }

    /**
     * Reset user password to default and create audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function resetPassword(User $user, array $meta): User
    {
        $payload = [
            'password' => $this->defaultPassword(),
            'last_password_changed_at' => null,
        ];

        $updatedUser = $this->repository->update($user, $payload);

        $this->auditLog('reset_user_password', "Reset password pengguna: {$updatedUser->name} ({$updatedUser->username}) ke default", $meta);

        return $updatedUser;
    }

    /**
     * Delete user and create audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function delete(User $user, array $meta): void
    {
        $info = "{$user->name} ({$user->username})";

        $this->repository->delete($user);

        $this->auditLog('delete_user', "Menghapus pengguna: {$info}", $meta);
    }

    /**
     * Check manager role uniqueness.
     */
    public function isManagerTaken(?string $excludeUserId = null): bool
    {
        return $this->repository->isManagerTaken($excludeUserId);
    }

    /**
     * Write audit log entry for user management actions.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
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

    private function defaultPassword(): string
    {
        $password = (string) config('auth.default_user_password');

        if (trim($password) === '') {
            throw new \RuntimeException('DEFAULT_USER_PASSWORD is not configured.');
        }

        return $password;
    }
}
