<?php

namespace App\Http\Requests;

use App\Enums\RoomOperationalStatus;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeetingRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;

        return [
            'code' => ['required', 'max:30', Rule::unique('meeting_rooms', 'code')->where('hotel_id', $hotelId)->ignore($this->route('meeting_room'))],
            'name' => ['required', 'max:255'],
            'floor' => ['nullable', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'operational_status' => ['required', Rule::enum(RoomOperationalStatus::class)],
            'facilities' => ['nullable', 'array'],
        ];
    }
}
