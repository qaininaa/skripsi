<?php

namespace App\Domains\Report\DTOs;

use Illuminate\Http\Request;

/**
 * DTO for analyst report drafting save action payload.
 */
class ReportDraftingSaveDTO
{
    /**
     * @param string $action
     * @param string|null $personnelAction
     * @param string|null $supervisorId
     * @param array $entries
     */
    public function __construct(
        public string $action,
        public ?string $personnelAction,
        public ?string $supervisorId,
        public array $entries,
    ) {}

    /**
     * Build DTO from HTTP request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            action: (string) $request->input('action', 'save'),
            personnelAction: $request->input('_personnel_action'),
            supervisorId: $request->input('supervisor_id'),
            entries: $request->input('entries', []),
        );
    }
}
