<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use App\Enums\MeetingStatus;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public const SOURCES = [
        'DIRECT',
        'PHONE',
        'EMAIL',
        'WALK_IN',
        'OTA',
        'CORPORATE',
        'EVENT_ORGANIZER',
        'PMS',
        'LEGACY',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;

        return [
            'external_booking_id' => ['nullable', 'max:255'],
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query
                    ->whereExists(fn ($exists) => $exists
                        ->selectRaw('1')
                        ->from('client_hotel')
                        ->whereColumn('client_hotel.client_id', 'clients.id')
                        ->where('client_hotel.hotel_id', $hotelId)
                        ->where('client_hotel.status', 'ACTIVE'))),
            ],
            'booking_number' => ['nullable', 'max:255', Rule::unique('bookings', 'booking_number')->where('hotel_id', $hotelId)->ignore($this->route('booking'))],
            'booking_source' => ['required', Rule::in(self::SOURCES)],
            'booking_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(BookingStatus::class)],
            'notes' => ['nullable', 'string'],
            'event_name' => ['nullable', 'required_with:event_date,start_at,end_at,meeting_room_id,package_id,expected_participants', 'max:255'],
            'event_date' => ['nullable', 'required_with:event_name,start_at,end_at,meeting_room_id,package_id,expected_participants', 'date'],
            'start_at' => ['nullable', 'required_with:event_name,event_date,end_at,meeting_room_id,package_id,expected_participants', 'date'],
            'end_at' => ['nullable', 'required_with:event_name,event_date,start_at,meeting_room_id,package_id,expected_participants', 'date', 'after:start_at'],
            'meeting_room_id' => ['nullable', 'required_with:event_name,event_date,start_at,end_at,package_id,expected_participants', Rule::exists('meeting_rooms', 'id')->where('hotel_id', $hotelId)],
            'package_id' => ['nullable', 'required_with:event_name,event_date,start_at,end_at,meeting_room_id,expected_participants', Rule::exists('meeting_packages', 'id')->where('hotel_id', $hotelId)->where('is_active', true)],
            'expected_participants' => ['nullable', 'required_with:event_name,event_date,start_at,end_at,meeting_room_id,package_id', 'integer', 'min:1'],
            'meeting_status' => ['nullable', Rule::enum(MeetingStatus::class)],
        ];
    }
}
