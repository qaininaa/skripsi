<?php

namespace App\Http\Requests\Masters\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'role' => ['required', Rule::in(['super', 'admin', 'analis', 'supervisor', 'manajer'])],
            'password' => ['required', 'string', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah ada.',
        ];
    }
}