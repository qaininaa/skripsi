<?php

namespace App\Domains\ReportAssignment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for report assignment create/update payload.
 */
class ReportAssignmentRequest extends FormRequest
{
    /**
     * Determine whether user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for assignment payload.
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
}
