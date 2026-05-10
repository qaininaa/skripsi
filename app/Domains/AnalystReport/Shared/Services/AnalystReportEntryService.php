<?php

namespace App\Domains\AnalystReport\Shared\Services;

use App\Domains\Report\Services\EnvironmentalEntryService;
use App\Domains\Report\Services\ReportEntryService;
use App\Models\Report;
use Illuminate\Http\Request;

/**
 * Domain-facing entry service for analyst report flow.
 *
 * This wrapper keeps AnalystReport module independent from direct usage of
 * global ReportEntryService while preserving existing behavior.
 */
class AnalystReportEntryService
{
    public function __construct(
        private ReportEntryService $entryService,
        private EnvironmentalEntryService $environmentalEntryService,
    ) {}

    /**
     * @param array $entries
     * @return array
     */
    public function validateCfu(array $entries): array
    {
        return $this->environmentalEntryService->validateCfu($entries);
    }

    /**
     * @param Report $report
     * @return void
     */
    public function migrateFieldOwners(Report $report): void
    {
        // Field ownership is no longer used.
    }

    /**
     * @param Request $request
     * @param Report $report
     * @return array
     */
    public function process(Request $request, Report $report): array
    {
        return $this->entryService->process($request, $report);
    }
}
