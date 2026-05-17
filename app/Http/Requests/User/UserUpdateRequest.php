<?php

namespace App\Http\Requests\User;

use Domain\User\Dtos\UpdateUserDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'role' => ['required', Rule::in(['super', 'admin', 'analyst', 'supervisor', 'manager'])],
            'password' => ['nullable', 'string', 'min:1', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah ada.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }

    public function toDTO(): UpdateUserDto
    {
        return UpdateUserDto::fromArray($this->validated());
    }
}
