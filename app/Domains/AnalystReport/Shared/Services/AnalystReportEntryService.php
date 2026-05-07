<?php

namespace App\Domains\AnalystReport\Shared\Services;

use App\Models\Report;
use App\Services\Reports\ReportEntryService as BaseReportEntryService;
use Illuminate\Http\Request;

/**
 * Domain-facing entry service for analyst report flow.
 *
 * This wrapper keeps AnalystReport module independent from direct usage of
 * global ReportEntryService while preserving existing behavior.
 */
class AnalystReportEntryService
{
    public function __construct(private BaseReportEntryService $baseService) {}

    /**
     * @param array $entries
     * @return array
     */
    public function validateCfu(array $entries): array
    {
        return $this->baseService->validateCfu($entries);
    }

    /**
     * @param Report $report
     * @return void
     */
    public function migrateFieldOwners(Report $report): void
    {
        $this->baseService->migrateFieldOwners($report);
    }

    /**
     * @param Request $request
     * @param Report $report
     * @return array
     */
    public function process(Request $request, Report $report): array
    {
        return $this->baseService->process($request, $report);
    }
}
