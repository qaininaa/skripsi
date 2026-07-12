<?php

namespace App\Http\Requests\Room;

use Domain\Room\Dtos\UpdateRoomDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for room update payload.
 */
class RoomUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for room update.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'room_name' => ['required', 'string', 'max:255'],
            'room_number' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Transform validated data into DTO.
     */
    public function toDTO(): UpdateRoomDto
    {
        return UpdateRoomDto::fromArray($this->validated());
    }
}
