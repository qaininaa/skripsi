<?php

namespace App\Http\Requests\Masters\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'nullable', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($this->route('user')),
            ],
            'role'     => ['required', Rule::in(['super', 'admin', 'analis', 'supervisor', 'manajer'])],
            'password' => ['nullable', 'string', 'confirmed'],
        ];
    }
}