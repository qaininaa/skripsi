<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for analyst password verification before sensitive actions.
 */
class ReportPasswordVerifyRequest extends FormRequest
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
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
