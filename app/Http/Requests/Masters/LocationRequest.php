<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'room_id'            => ['required', 'exists:rooms,id'],
            'frequency'          => ['nullable', 'in:operational,daily,weekly,monthly,semi_annual'],
            'location_number'    => ['nullable', 'string', 'max:50'],
            'measurement_type'   => ['nullable', 'string', 'max:50'],
            'alert_limit_total'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_limit_fungi'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_action_total' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'alert_action_fungi' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}