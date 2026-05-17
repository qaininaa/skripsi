<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\ReportSubmissionRepositoryInterface;
use Domain\User\Models\User;

/**
 * Eloquent implementation of ReportSubmissionRepositoryInterface.
 */
class ReportSubmissionRepository implements ReportSubmissionRepositoryInterface
{
    public function supervisorExists(string $supervisorId): bool
    {
        return User::where('id', $supervisorId)
            ->where('role', 'supervisor')
            ->exists();
    }
}
