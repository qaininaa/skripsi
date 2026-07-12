<?php

namespace Domain\Report\Interfaces;

use Illuminate\Support\Collection;

interface ReportDashboardRepositoryInterface
{
    /**
     * Fetch TMS section rows from reports that were approved by manager.
     *
     * @return Collection<int, object>
     */
    public function getApprovedManagerTmsSections(string $managerId): Collection;
}

