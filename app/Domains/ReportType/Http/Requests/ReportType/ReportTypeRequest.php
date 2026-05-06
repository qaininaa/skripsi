<?php

namespace App\Domains\ReportType\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ReportTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), $this->extraRules());
    }

    public function messages(): array
    {
        return [
            'sop_code.unique' => 'Jenis laporan sudah ada.',
        ];
    }

    protected function baseRules(): array
    {
        return [
            'sop_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('report_types', 'sop_code')
                    ->where('annex_number', $this->input('annex_number'))
                    ->where('sop_version', $this->input('sop_version'))
                    ->ignore($this->route('report_type')),
            ],
            'sop_version' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'annex_number' => ['required', 'integer', 'min:1'],
            'medium_labels' => ['nullable', 'array'],
            'medium_labels.*' => ['nullable', 'string', 'max:255'],
            'incubator_labels' => ['nullable', 'array'],
            'incubator_labels.*' => ['nullable', 'string', 'max:255'],
            'incubator_min_days' => ['nullable', 'array'],
            'incubator_min_days.*' => ['nullable', 'integer', 'min:1'],
            'has_personnel' => ['nullable', 'boolean'],
        ];
    }

    protected function extraRules(): array
    {
        return [];
    }
}
