<?php

namespace App\Http\Requests\ReportType;

use Domain\ReportType\Dtos\CreateReportTypeDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for report type creation payload.
 */
class ReportTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for report type creation.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'sop_code' => ['required', 'string', 'max:100'],
            'sop_version' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'annex_number' => ['required', 'integer', 'min:1'],
            'medium_labels' => ['required', 'array'],
            'medium_labels.*' => ['required', 'string', 'max:255'],
            'incubator_labels' => ['required', 'array'],
            'incubator_labels.*' => ['required', 'string', 'max:255'],
            'incubator_min_days' => ['required', 'array'],
            'incubator_min_days.*' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'annex_number.required' => 'Nomor annex wajib diisi.',
            'annex_number.integer' => 'Nomor annex harus berupa angka bulat.',
            'annex_number.min' => 'Nomor annex minimal 1.',

            'medium_labels.required' => 'Minimal satu medium wajib diisi.',
            'medium_labels.array' => 'Format medium tidak valid.',
            'medium_labels.*.max' => 'Label medium maksimal 255 karakter.',

            'incubator_labels.required' => 'Minimal satu inkubator wajib diisi.',
            'incubator_labels.array' => 'Format inkubator tidak valid.',
            'incubator_labels.*.max' => 'Label inkubator maksimal 255 karakter.',

            'incubator_min_days.required' => 'Durasi minimum inkubator wajib diisi.',
            'incubator_min_days.array' => 'Format durasi minimum inkubator tidak valid.',
            'incubator_min_days.*.integer' => 'Hari minimal inkubator harus berupa angka.',
            'incubator_min_days.*.min' => 'Hari minimal inkubator minimal 1.',
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): CreateReportTypeDto
    {
        return CreateReportTypeDto::fromArray($this->validated());
    }
}
