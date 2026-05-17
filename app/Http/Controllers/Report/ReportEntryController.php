<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportEntrySaveRequest;
use Domain\Report\Models\EntryReport;
use Domain\Report\Services\ReportEntryService;
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
    public function save(ReportEntrySaveRequest $request, EntryReport $report): JsonResponse
    {
        $dto = $request->toDTO();
        $entryRequest = new Request($dto->toProcessPayload());

        [$savedSectionIds] = $this->service->process($entryRequest, $report);

        return response()->json([
            'ok' => true,
            'savedSectionIds' => $savedSectionIds,
        ]);
    }
}
