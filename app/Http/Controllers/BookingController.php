<?php

namespace App\Http\Controllers;

use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('client')->orderByDesc('booking_date')->paginate(25);

        return $request->wantsJson() ? response()->json($bookings) : view('domain.bookings.index', compact('bookings'));
    }

    public function create()
    {
        return view('domain.bookings.create', [
            'booking' => new Booking,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        abort_if($hotelId === null, 422, 'Select a hotel context before creating a booking.');

        $booking = Booking::create(array_merge($request->validated(), [
            'hotel_id' => $hotelId,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]));

        return redirect()->route('bookings.show', $booking)->with('status', 'Booking created.');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('domain.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $this->authorize('update', $booking);

        return view('domain.bookings.edit', [
            'booking' => $booking,
            'clients' => Client::orderBy('company_name')->get(),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $this->authorize('update', $booking);
        $booking->update(array_merge($request->validated(), ['updated_by' => $request->user()->id]));

        return redirect()->route('bookings.show', $booking)->with('status', 'Booking updated.');
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);
        $booking->update(['status' => 'CANCELLED']);

        return redirect()->route('bookings.index')->with('status', 'Booking cancelled.');
    }
}
