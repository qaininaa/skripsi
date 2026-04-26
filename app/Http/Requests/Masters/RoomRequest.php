<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'room_name'   => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:100'],
            'class'       => ['required', 'string', 'max:50'],
        ];
    }
}