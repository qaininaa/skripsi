<?php

namespace App\Http\Requests\PasswordPolicy;

use Domain\PasswordPolicy\Dtos\PasswordSettingDto;
use Illuminate\Foundation\Http\FormRequest;

class PasswordPolicyUpdateRequest extends FormRequest
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
            'password_expiration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'password_history_count' => ['required', 'integer', 'min:1', 'max:24'],
        ];
    }

    public function toDTO(): PasswordSettingDto
    {
        return PasswordSettingDto::fromArray($this->validated());
    }
}
