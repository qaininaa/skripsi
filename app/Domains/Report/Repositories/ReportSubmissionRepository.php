<?php

namespace App\Domains\Report\Repositories;

use App\Models\User;

/**
 * Repository for analyst submission checks.
 */
class ReportSubmissionRepository
{
    public function supervisorExists(string $supervisorId): bool
    {
        return User::where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->exists();
    }
}
