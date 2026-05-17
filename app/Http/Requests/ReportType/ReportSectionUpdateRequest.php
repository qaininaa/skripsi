<?php

namespace App\Http\Requests\ReportType;

use Domain\ReportType\Dtos\UpdateReportSectionDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for report section update payload.
 */
class ReportSectionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for report section update.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'measurement_unit' => ['required', 'string', 'max:50'],
            'measurement_type' => ['required', 'string', 'max:50'],
            'max_column' => ['required', 'integer', 'min:1', 'max:20'],
            'column_label' => ['required', 'string', 'max:50'],
            'time_slot_type' => ['required', 'string', 'in:none,single,per_location,dual_ab,swab'],
            'has_machine_setup' => ['boolean'],
            'order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): UpdateReportSectionDto
    {
        return UpdateReportSectionDto::fromArray($this->validated());
    }
}
