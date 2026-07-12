<?php

namespace App\Http\Requests\ReportAssignment;

use Domain\ReportAssignment\Dtos\CreateReportAssignmentDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for report assignment creation payload.
 */
class ReportAssignmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for assignment creation.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'batch_number' => ['required', 'string', 'max:255'],
            'report_type_id' => ['required', 'exists:report_types,id'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): CreateReportAssignmentDto
    {
        return CreateReportAssignmentDto::fromArray($this->validated());
    }
}
