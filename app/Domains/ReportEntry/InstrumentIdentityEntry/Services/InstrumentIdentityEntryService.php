<?php

namespace App\Domains\ReportEntry\InstrumentIdentityEntry\Services;

use App\Domains\ReportEntry\InstrumentIdentityEntry\DTOs\InstrumentIdentityEntryData;
use App\Domains\ReportEntry\InstrumentIdentityEntry\Repositories\InstrumentIdentityEntryRepository;
use App\Models\Report;
use Illuminate\Http\Request;

/**
 * Service for instrument identity entry persistence.
 */
class InstrumentIdentityEntryService
{
    public function __construct(private InstrumentIdentityEntryRepository $repository) {}

    public function saveFromRequest(Request $request, Report $report): void
    {
        if (! $request->has('air_sampler')) {
            return;
        }

        $payload = (array) $request->input('air_sampler', []);
        $this->saveFromArray($payload, $report);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveFromArray(array $payload, Report $report): void
    {
        $data = InstrumentIdentityEntryData::fromArray($payload);

        $this->repository->upsertForReport($report, $data);
    }
}
