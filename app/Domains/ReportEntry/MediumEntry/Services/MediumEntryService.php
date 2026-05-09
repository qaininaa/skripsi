<?php

namespace App\Domains\ReportEntry\MediumEntry\Services;

use App\Domains\ReportEntry\MediumEntry\DTOs\MediumEntryData;
use App\Domains\ReportEntry\MediumEntry\Repositories\MediumEntryRepository;
use App\Models\Report;
use Illuminate\Http\Request;

/**
 * Service for medium identity entry persistence.
 */
class MediumEntryService
{
    public function __construct(
        private MediumEntryRepository $repository,
    ) {}

    public function saveFromRequest(Request $request, Report $report): void
    {
        if (! $request->has('medium')) {
            return;
        }

        $this->saveFromArray((array) $request->input('medium', []), $report);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveFromArray(array $payload, Report $report): void
    {
        if ($payload === []) {
            return;
        }

        $report->loadMissing('reportType.mediumTypes');

        foreach ($payload as $name => $item) {
            if (! is_array($item)) {
                continue;
            }

            $data = MediumEntryData::fromArray((string) $name, $item);
            if ($data->name === '') {
                continue;
            }

            $mediumType = $report->reportType->mediumTypes->firstWhere('name', $data->name);
            if (! $mediumType) {
                continue;
            }

            $this->repository->updateOrCreateByName(
                $report,
                $data->name,
                [
                    'medium_id' => $mediumType->id,
                    'batch_number' => $data->batchNumber,
                    'gpt_number' => $data->gptNumber,
                    'expiration_date' => $data->expirationDate,
                ]
            );
        }
    }
}
