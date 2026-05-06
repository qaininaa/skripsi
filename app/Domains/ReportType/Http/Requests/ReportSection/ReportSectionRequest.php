<?php

namespace App\Domains\ReportType\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ReportSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), $this->extraRules());
    }

    protected function baseRules(): array
    {
        return [
            'measurement_unit' => ['required', 'string', 'max:50'],
            'measurement_type' => ['required', 'string', 'max:50'],
            'max_column' => ['required', 'integer', 'min:1', 'max:20'],
            'column_label' => ['nullable', 'string', 'max:50'],
            'time_slot_type' => ['required', 'string', 'in:none,single,per_location,dual_ab,swab'],
            'has_machine_setup' => ['boolean'],
        ];
    }

    protected function extraRules(): array
    {
        return [];
    }
}
