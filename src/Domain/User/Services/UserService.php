<?php

namespace Domain\User\Services;

use Domain\AuditLog\Services\AuditLogService;
use Domain\User\Dtos\CreateUserDto;
use Domain\User\Dtos\GetUsersFilterDto;
use Domain\User\Interfaces\UserRepositoryInterface;
use Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Handles business logic for user management.
 */
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Retrieve paginated users for management page.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getDataUsers(GetUsersFilterDto $dto, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getUsers($dto, $perPage);
    }

    /**
     * Create new user with default password and write audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function createUser(CreateUserDto $dto, array $meta): User
    {
        $payload = $dto->toArray();
        $payload['password'] = $this->defaultPassword();
        $payload['last_password_changed_at'] = null;

        $user = $this->repository->create($payload);

        $this->auditLogService->log(
            'create_user',
            "Membuat pengguna baru: {$user->name} ({$user->username})",
            $meta
        );

        return $user;
    }

    /**
     * Reset user password to default.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function resetPassword(User $user, array $meta): User
    {
        $updatedUser = $this->repository->update($user, [
            'password' => $this->defaultPassword(),
            'last_password_changed_at' => null,
        ]);

        $this->auditLogService->log(
            'reset_user_password',
            "Reset password pengguna: {$updatedUser->name} ({$updatedUser->username}) ke default",
            $meta
        );

        return $updatedUser;
    }

    /**
     * Delete a user and write audit log.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function deleteUser(User $user, array $meta): void
    {
        $info = "{$user->name} ({$user->username})";

        $this->repository->delete($user);

        $this->auditLogService->log('delete_user', "Menghapus pengguna: {$info}", $meta);
    }

    /**
     * Check whether a manager role is already assigned.
     */
    public function isManagerTaken(?string $excludeUserId = null): bool
    {
        return $this->repository->isManagerTaken($excludeUserId);
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
