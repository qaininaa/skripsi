<?php

namespace App\Domains\Report\Http\Controllers;

use App\Domains\Report\DTOs\ReportEntrySaveDTO;
use App\Domains\Report\Models\EntryReport;
use App\Domains\Report\Services\ReportEntryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller dedicated for report entry persistence endpoint.
 */
class ReportEntryController extends Controller
{
    public function __construct(private ReportEntryService $service) {}

    /**
     * Save entry payload only (environmental + personnel) for a report.
     */
    public function save(Request $request, EntryReport $report): JsonResponse
    {
        $dto = ReportEntrySaveDTO::fromRequest($request);
        $entryRequest = new Request($dto->toProcessPayload());

        [$savedSectionIds, $hasPersonnelData] = $this->service->process($entryRequest, $report);

        return response()->json([
            'ok' => true,
            'savedSectionIds' => $savedSectionIds,
            'hasPersonnelData' => $hasPersonnelData,
        ]);
    }
}
