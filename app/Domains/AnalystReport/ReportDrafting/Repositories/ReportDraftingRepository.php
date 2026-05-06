<?php

namespace App\Domains\AnalystReport\ReportDrafting\Repositories;

use App\Models\User;

/**
 * Repository for analyst report drafting checks.
 */
class ReportDraftingRepository
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
