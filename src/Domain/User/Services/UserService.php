<?php

namespace Domain\User\Services;

use Domain\AuditLog\Services\AuditLogService;
use Domain\User\Dtos\CreateUserDto;
use Domain\User\Dtos\GetUsersFilterDto;
use Domain\User\Dtos\UpdateUserDto;
use Domain\User\Interfaces\UserRepositoryInterface;
use Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

/**
 * Handles business logic for user management.
 */
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private AuditLogService $auditLogService,
        private PasswordService $passwordService,
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
     * Create a new user with admin-supplied password.
     *
     * last_password_changed_at is set to null so the user is required to
     * change their password on first login.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function createUser(CreateUserDto $dto, array $meta): User
    {
        $user = $this->repository->create([
            'name' => $dto->name,
            'username' => $dto->username,
            'role' => $dto->role,
            'password' => Hash::make($dto->password),
            'last_password_changed_at' => null,
        ]);

        $this->auditLogService->log(
            'create_user',
            "Membuat pengguna baru: {$user->name} ({$user->username})",
            $meta
        );

        return $user;
    }

    /**
     * Update existing user attributes. If the DTO carries a password,
     * it is also reset (current password archived to history) and the
     * user is forced to change password on next login.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function updateUser(User $user, UpdateUserDto $dto, array $meta): User
    {
        $updatedUser = $this->repository->update($user, [
            'name' => $dto->name,
            'username' => $dto->username,
            'role' => $dto->role,
        ]);

        if ($dto->hasPasswordReset()) {
            // Archive current password into history so the user cannot reuse it.
            $this->passwordService->resetByAdmin($updatedUser, $dto->password);
        }

        $this->auditLogService->log(
            'update_user',
            "Memperbarui pengguna: {$updatedUser->name} ({$updatedUser->username})"
                . ($dto->hasPasswordReset() ? ' (password direset)' : ''),
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
     * Check whether a manager role is already assigned, optionally excluding a user id.
     */
    public function isManagerTaken(?string $excludeUserId = null): bool
    {
        return $this->repository->isManagerTaken($excludeUserId);
    }
}
