<?php

namespace App\Http\Requests\Report;

use Domain\Report\Dtos\ReportClaimingFilterDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for analyst report listing filter.
 */
class ReportClaimingIndexRequest extends FormRequest
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
            'status' => ['nullable', 'string'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): ReportClaimingFilterDto
    {
        return ReportClaimingFilterDto::fromArray($this->validated());
    }
}
