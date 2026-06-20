<?php

namespace App\Http\Requests;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\RoomOperationalStatus;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMeetingRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hotelId = $this->targetHotelId();

        return [
            'hotel_id' => [
                $this->user()->isSuperAdmin() ? ($hotelId ? 'nullable' : 'required') : 'prohibited',
                'integer',
                Rule::exists('hotels', 'id')->where('status', 'ACTIVE'),
            ],
            'code' => ['required', 'max:30', Rule::unique('meeting_rooms', 'code')->where('hotel_id', $hotelId)->ignore($this->route('meeting_room'))],
            'name' => ['required', 'max:255'],
            'floor' => ['nullable', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'operational_status' => ['required', Rule::enum(RoomOperationalStatus::class)],
            'facilities' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $room = $this->route('meeting_room');

            if (! $room || ! $this->filled('hotel_id') || (int) $this->input('hotel_id') === (int) $room->hotel_id) {
                return;
            }

            $hasDependentMeetings = MeetingEvent::withoutGlobalScope('hotel')
                ->where('meeting_room_id', $room->id)
                ->exists();

            if ($hasDependentMeetings) {
                $validator->errors()->add('hotel_id', 'The hotel cannot be changed because this room already has meetings.');
            }
        });
    }

    public function targetHotelId(): ?int
    {
        if ($this->user()->isSuperAdmin() && $this->filled('hotel_id')) {
            return (int) $this->input('hotel_id');
        }

        return app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;
    }
}
