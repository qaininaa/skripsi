<?php

namespace App\Http\Requests\Report;

use Domain\Report\Dtos\ReportEntrySaveDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for direct report entry persistence endpoint.
 */
class ReportEntrySaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'entries' => ['nullable', 'array'],
            'settle_times' => ['nullable', 'array'],
            'swab_times' => ['nullable', 'array'],
            'exposure_times' => ['nullable', 'array'],
            'column_names' => ['nullable', 'array'],
            'air_sampler' => ['nullable', 'array'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): ReportEntrySaveDto
    {
        return ReportEntrySaveDto::fromArray($this->validated());
    }
}
