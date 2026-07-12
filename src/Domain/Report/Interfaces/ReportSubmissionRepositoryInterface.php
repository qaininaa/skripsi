<?php

namespace Domain\Report\Interfaces;

/**
 * Contract for analyst submission checks.
 */
interface ReportSubmissionRepositoryInterface
{
    public function supervisorExists(string $supervisorId): bool;
}
