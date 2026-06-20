<?php

namespace App\Http\Controllers;

use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Support\Audit\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('client')->orderByDesc('booking_date')->paginate(25);

        return $request->wantsJson() ? response()->json($bookings) : $this->viewOrRedirect($request, 'domain.bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: request()->user()->hotel_id;

        return $this->viewOrRedirect($request, 'domain.bookings.create', [
            'booking' => new Booking,
            'clients' => Client::when($hotelId, fn ($query) => $query->associatedWithHotel((int) $hotelId))->orderBy('company_name')->get(),
        ]);
    }

    public function store(StoreBookingRequest $request, AuditLogger $auditLogger)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        abort_if($hotelId === null, 422, 'Select a hotel context before creating a booking.');

        $booking = Booking::create(array_merge($request->validated(), [
            'hotel_id' => $hotelId,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]));
        $auditLogger->record('booking.created', $booking->hotel_id, $request->user()->id, $booking, [], [], $booking->toArray());

        return redirect()->route('bookings.show', $booking)->with('status', 'Booking created.');
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        return $this->viewOrRedirect($request, 'domain.bookings.show', compact('booking'));
    }

    public function edit(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        return $this->viewOrRedirect($request, 'domain.bookings.edit', [
            'booking' => $booking,
            'clients' => Client::when($booking->hotel_id, fn ($query) => $query->associatedWithHotel((int) $booking->hotel_id))->orderBy('company_name')->get(),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking, AuditLogger $auditLogger)
    {
        $this->authorize('update', $booking);
        $before = $booking->toArray();
        $booking->update(array_merge($request->validated(), ['updated_by' => $request->user()->id]));
        $event = (int) ($before['client_id'] ?? 0) !== (int) $booking->client_id ? 'booking.client_changed' : 'booking.updated';
        $auditLogger->record($event, $booking->hotel_id, $request->user()->id, $booking, [], $before, $booking->fresh()->toArray());

        return redirect()->route('bookings.show', $booking)->with('status', 'Booking updated.');
    }

    public function destroy(Request $request, Booking $booking, AuditLogger $auditLogger)
    {
        $this->authorize('delete', $booking);
        $before = $booking->toArray();
        $booking->update(['status' => 'CANCELLED']);
        $auditLogger->record('booking.cancelled', $booking->hotel_id, $request->user()->id, $booking, [], $before, $booking->fresh()->toArray());

        return redirect()->route('bookings.index')->with('status', 'Booking cancelled.');
    }
}
