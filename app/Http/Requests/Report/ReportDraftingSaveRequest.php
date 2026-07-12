<?php

namespace App\Http\Requests\Report;

use Domain\Report\Dtos\ReportDraftingSaveDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for analyst report drafting save action.
 */
class ReportDraftingSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Loose validation: the form carries deeply nested arrays and binary form
     * data. Domain validation (CFU rules, supervisor existence, etc.) happens
     * inside the Service layer.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'action' => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'uuid'],
            'entries' => ['nullable', 'array'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): ReportDraftingSaveDto
    {
        return ReportDraftingSaveDto::fromArray($this->validated(), $this->all());
    }
}
