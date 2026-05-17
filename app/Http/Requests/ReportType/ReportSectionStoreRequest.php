<?php

namespace App\Http\Requests\ReportType;

use Domain\ReportType\Dtos\CreateReportSectionDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for report section creation payload.
 */
class ReportSectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for report section creation.
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
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): CreateReportSectionDto
    {
        return CreateReportSectionDto::fromArray($this->validated());
    }
}
