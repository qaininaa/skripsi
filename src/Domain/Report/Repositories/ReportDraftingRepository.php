<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\ReportDraftingRepositoryInterface;
use Domain\User\Models\User;

/**
 * Eloquent implementation of ReportDraftingRepositoryInterface.
 */
class ReportDraftingRepository implements ReportDraftingRepositoryInterface
{
    public function supervisorExists(string $supervisorId): bool
    {
        return User::where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->exists();
    }
}
