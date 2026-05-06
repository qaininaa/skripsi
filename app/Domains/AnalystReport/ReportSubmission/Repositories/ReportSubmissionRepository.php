<?php

namespace App\Domains\AnalystReport\ReportSubmission\Repositories;

use App\Models\User;

/**
 * Repository for analyst submission checks.
 */
class ReportSubmissionRepository
{
    /**
     * Check if selected supervisor exists.
     *
     * @param string $supervisorId
     * @return bool
     */
    public function supervisorExists(string $supervisorId): bool
    {
        return User::where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->exists();
    }
}
