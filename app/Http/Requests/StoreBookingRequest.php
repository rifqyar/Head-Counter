<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
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
            'booking_number' => ['required', 'max:255', Rule::unique('bookings', 'booking_number')->where('hotel_id', $hotelId)->ignore($this->route('booking'))],
            'booking_source' => ['required', 'max:255'],
            'booking_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(BookingStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
