<?php

namespace App\Http\Requests;

use App\Enums\MeetingStatus;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeetingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;

        return [
            'booking_id' => ['nullable', Rule::exists('bookings', 'id')->where('hotel_id', $hotelId)],
            'meeting_room_id' => ['nullable', Rule::exists('meeting_rooms', 'id')->where('hotel_id', $hotelId)],
            'event_name' => ['nullable', 'required_without:booking_id', 'max:255'],
            'event_date' => ['nullable', 'required_without:booking_id', 'date'],
            'start_at' => ['nullable', 'required_without:booking_id', 'date'],
            'end_at' => ['nullable', 'required_without:booking_id', 'date', 'after:start_at'],
            'expected_participants' => ['nullable', 'required_without:booking_id', 'integer', 'min:0'],
            'actual_participants' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'required_without:booking_id', Rule::enum(MeetingStatus::class)],
        ];
    }
}
