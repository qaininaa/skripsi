<?php

namespace App\Domains\ReportEntry\Services;

use App\Domains\ReportEntry\DTOs\ReportEntrySaveDTO;
use App\Domains\ReportEntry\Models\EntryReport;
use App\Services\Reports\ReportEntryService as BaseReportEntryService;
use Illuminate\Http\Request;

/**
 * Domain service for saving entry-only payloads.
 */
class ReportEntrySaveService
{
    public function __construct(private BaseReportEntryService $baseService) {}

    /**
     * @return array{savedSectionIds: array, hasPersonnelData: bool}
     */
    public function save(EntryReport $report, ReportEntrySaveDTO $dto): array
    {
        $request = new Request($dto->toProcessPayload());

        [$savedSectionIds, $hasPersonnelData] = $this->baseService->process($request, $report);

        return [
            'savedSectionIds' => $savedSectionIds,
            'hasPersonnelData' => $hasPersonnelData,
        ];
    }
}
