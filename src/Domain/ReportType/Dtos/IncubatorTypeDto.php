<?php

namespace Domain\ReportType\Dtos;

/**
 * Pair of incubator label and minimum incubation days.
 */
class IncubatorTypeDto
{
    public function __construct(
        public readonly string $label,
        public readonly int $minDay,
    ) {}
}
