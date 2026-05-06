<?php

namespace App\Domains\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'role' => ['required', Rule::in(['super', 'admin', 'analis', 'supervisor', 'manajer'])],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah ada.',
        ];
    }
}
