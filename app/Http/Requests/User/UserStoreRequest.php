<?php

namespace App\Http\Requests\User;

use Domain\User\Dtos\CreateUserDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'role' => ['required', Rule::in(['super', 'admin', 'analis', 'supervisor', 'manajer'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah ada.',
        ];
    }

    public function toDTO(): CreateUserDto
    {
        return CreateUserDto::fromArray($this->validated());
    }
}
