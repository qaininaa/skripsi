<?php

namespace Domain\Report\Dtos;

/**
 * DTO for report entry payload.
 */
class ReportEntrySaveDto
{
    /**
     * @param array<string, mixed> $entries
     * @param array<string, mixed> $settleTimes
     * @param array<string, mixed> $swabTimes
     * @param array<string, mixed> $exposureTimes
     * @param array<string, mixed> $columnNames
     * @param array<string, mixed> $airSampler
     */
    public function __construct(
        public array $entries,
        public array $settleTimes,
        public array $swabTimes,
        public array $exposureTimes,
        public array $columnNames,
        public array $airSampler,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            entries: (array) ($validated['entries'] ?? []),
            settleTimes: (array) ($validated['settle_times'] ?? []),
            swabTimes: (array) ($validated['swab_times'] ?? []),
            exposureTimes: (array) ($validated['exposure_times'] ?? []),
            columnNames: (array) ($validated['column_names'] ?? []),
            airSampler: (array) ($validated['air_sampler'] ?? []),
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
        ];
    }
}
