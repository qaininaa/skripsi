<?php

namespace Domain\User\Interfaces;

use Domain\User\Models\User;
use Illuminate\Support\Collection;

/**
 * Contract for password history persistence per user.
 */
interface PasswordRepositoryInterface
{
    /**
     * Fetch recent password histories for a user (limited to history policy).
     *
     * @return Collection<int, \Domain\User\Models\PasswordHistory>
     */
    public function recentHistories(User $user, int $limit): Collection;

    /**
     * Append the given hashed password to user history.
     */
    public function addHistory(User $user, string $hashedPassword): void;

    /**
     * Trim history entries beyond the configured limit.
     */
    public function pruneHistories(User $user, int $limit): void;

    /**
     * Persist new hashed password and update last changed timestamp.
     */
    public function updateUserPassword(User $user, string $hashedPassword): void;
}
