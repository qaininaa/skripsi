<?php

namespace App\Http\Requests\Auth;

use App\Rules\PasswordComplexity;
use Domain\User\Dtos\PasswordChangeDto;
use Illuminate\Foundation\Http\FormRequest;

class PasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', new PasswordComplexity],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }

    public function toDTO(): PasswordChangeDto
    {
        return PasswordChangeDto::fromArray($this->validated());
    }
}
