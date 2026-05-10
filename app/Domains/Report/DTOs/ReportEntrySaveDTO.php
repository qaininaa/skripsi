<?php

namespace App\Domains\Report\DTOs;

use Illuminate\Http\Request;

/**
 * DTO for report entry payload.
 */
class ReportEntrySaveDTO
{
    /**
     * @param array $entries
     * @param array $settleTimes
     * @param array $swabTimes
     * @param array $exposureTimes
     * @param array $columnNames
     * @param array $airSampler
     * @param array $personnel
     * @param array $pageNotes
     */
    public function __construct(
        public array $entries,
        public array $settleTimes,
        public array $swabTimes,
        public array $exposureTimes,
        public array $columnNames,
        public array $airSampler,
        public array $personnel,
        public array $pageNotes,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            entries: (array) $request->input('entries', []),
            settleTimes: (array) $request->input('settle_times', []),
            swabTimes: (array) $request->input('swab_times', []),
            exposureTimes: (array) $request->input('exposure_times', []),
            columnNames: (array) $request->input('column_names', []),
            airSampler: (array) $request->input('air_sampler', []),
            personnel: (array) $request->input('personnel', []),
            pageNotes: (array) $request->input('page_notes', []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toProcessPayload(): array
    {
        return [
            'entries' => $this->entries,
            'settle_times' => $this->settleTimes,
            'swab_times' => $this->swabTimes,
            'exposure_times' => $this->exposureTimes,
            'column_names' => $this->columnNames,
            'air_sampler' => $this->airSampler,
            'personnel' => $this->personnel,
            'page_notes' => $this->pageNotes,
        ];
    }
}
