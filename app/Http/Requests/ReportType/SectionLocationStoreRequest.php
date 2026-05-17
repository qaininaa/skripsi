<?php

namespace App\Http\Requests\ReportType;

use Domain\ReportType\Dtos\AssignSectionLocationDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for assigning a location to a report section.
 */
class SectionLocationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the assignment payload.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): AssignSectionLocationDto
    {
        return AssignSectionLocationDto::fromArray($this->validated());
    }
}
