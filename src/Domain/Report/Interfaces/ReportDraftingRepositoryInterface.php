<?php

namespace Domain\Report\Interfaces;

/**
 * Contract for analyst report drafting checks.
 */
interface ReportDraftingRepositoryInterface
{
    public function supervisorExists(string $supervisorId): bool;
}
