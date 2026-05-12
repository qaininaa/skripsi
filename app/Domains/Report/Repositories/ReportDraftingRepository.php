<?php

namespace App\Domains\Report\Repositories;

use App\Domains\User\Models\User;

/**
 * Repository for analyst report drafting checks.
 */
class ReportDraftingRepository
{
    public function supervisorExists(string $supervisorId): bool
    {
        return User::where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->exists();
    }
}
