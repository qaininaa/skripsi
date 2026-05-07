<?php

namespace App\Domains\ReportEntry\Http\Controllers;

use App\Domains\ReportEntry\DTOs\ReportEntrySaveDTO;
use App\Domains\ReportEntry\Models\EntryReport;
use App\Domains\ReportEntry\Services\ReportEntrySaveService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller dedicated for report entry persistence endpoint.
 */
class ReportEntryController extends Controller
{
    public function __construct(private ReportEntrySaveService $service) {}

    /**
     * Save entry payload only (environmental + personnel) for a report.
     */
    public function save(Request $request, EntryReport $report): JsonResponse
    {
        $dto = ReportEntrySaveDTO::fromRequest($request);

        $result = $this->service->save($report, $dto);

        return response()->json([
            'ok' => true,
            'savedSectionIds' => $result['savedSectionIds'],
            'hasPersonnelData' => $result['hasPersonnelData'],
        ]);
    }
}
