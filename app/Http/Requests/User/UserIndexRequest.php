<?php

namespace App\Http\Requests\User;

use Domain\User\Dtos\GetUsersFilterDto;
use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): GetUsersFilterDto
    {
        $validated = $this->validated();

        return new GetUsersFilterDto(
            search: $validated['search'] ?? null,
            role: $validated['role'] ?? null,
        );
    }
}
