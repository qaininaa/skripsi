<?php

namespace App\Domains\ReportType\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
        ];
    }
}
