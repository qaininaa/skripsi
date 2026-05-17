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

    public function toDTO(): PasswordChangeDto
    {
        return PasswordChangeDto::fromArray($this->validated());
    }
}
