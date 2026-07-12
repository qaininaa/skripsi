<?php

namespace App\Http\Requests\Auth;

use Domain\User\Dtos\LoginDto;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function toDTO(): LoginDto
    {
        return LoginDto::fromArray([
            ...$this->validated(),
            'remember' => $this->boolean('remember'),
        ]);
    }
}
