<?php

namespace App\Domains\AnalystReport\ReportClaiming\DTOs;

use Illuminate\Http\Request;

/**
 * DTO for analyst report listing filter.
 */
class ReportClaimingFilterDTO
{
    /**
     * @param string $status
     */
    public function __construct(public string $status) {}

    /**
     * Build DTO from HTTP request query.
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self((string) $request->query('status', 'all'));
    }
}
