<?php

namespace App\Http\Controllers;

use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Catering\MeetingPackageAssignment;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\QRCode\MeetingQRService;
use App\Enums\BookingStatus;
use App\Enums\MeetingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Services\MeetingRoomConflictService;
use App\Services\MeetingStateTransition;
use App\Support\Audit\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with(['client', 'meetingEvents'])->orderByDesc('booking_date')->paginate(25);

        return $request->wantsJson() ? response()->json($bookings) : $this->viewOrRedirect($request, 'domain.bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: request()->user()->hotel_id;

        return $this->viewOrRedirect($request, 'domain.bookings.create', [
            'booking' => new Booking,
            'clients' => Client::when($hotelId, fn ($query) => $query->associatedWithHotel((int) $hotelId))->orderBy('company_name')->get(),
            'rooms' => MeetingRoom::when($hotelId, fn ($query) => $query->where('hotel_id', $hotelId))->orderBy('code')->get(),
            'packages' => MeetingPackage::when($hotelId, fn ($query) => $query->where('hotel_id', $hotelId))->where('is_active', true)->with('entitlements')->orderBy('code')->get(),
            'sources' => StoreBookingRequest::SOURCES,
        ]);
    }

    public function store(StoreBookingRequest $request, AuditLogger $auditLogger, MeetingRoomConflictService $conflicts, MeetingStateTransition $stateTransition, MeetingQRService $meetingQRService)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        abort_if($hotelId === null, 422, 'Select a hotel context before creating a booking.');

        $validated = $request->validated();
        if ($this->hasMeetingPayload($validated)) {
            $conflict = $conflicts->findConflict((int) $hotelId, (int) $validated['meeting_room_id'], $validated['start_at'], $validated['end_at']);
            if ($conflict) {
                return back()->withInput()->withErrors(['meeting_room_id' => "Room is already assigned to {$conflict->event_name}."]);
            }
        }

        $booking = DB::transaction(function () use ($validated, $hotelId, $request, $auditLogger, $stateTransition, $meetingQRService) {
            $bookingData = Arr::only($validated, ['external_booking_id', 'client_id', 'booking_number', 'booking_source', 'booking_date', 'status', 'notes']);
            $bookingData['booking_number'] = ($bookingData['booking_number'] ?? null) ?: $this->nextBookingNumber((int) $hotelId);
            $booking = Booking::create(array_merge($bookingData, [
                'hotel_id' => $hotelId,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]));

            $meeting = $this->hasMeetingPayload($validated)
                ? $this->upsertPrimaryMeeting($booking, $validated, $request->user()->id, $stateTransition)
                : null;

            if ($meeting && $booking->status === BookingStatus::CONFIRMED) {
                $meetingQRService->generate($meeting->fresh(), $request->user()->id);
            }

            $auditLogger->record('booking.created', $booking->hotel_id, $request->user()->id, $booking, [], [], $booking->toArray());

            return $booking;
        });

        return redirect()->route('bookings.show', $booking)->with('status', 'Booking created.');
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['hotel', 'client', 'meetingEvents.meetingRoom', 'meetingEvents.packageAssignments.package.entitlements']);

        return $this->viewOrRedirect($request, 'domain.bookings.show', compact('booking'));
    }

    public function edit(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);
        $booking->load('meetingEvents.packageAssignments');

        return $this->viewOrRedirect($request, 'domain.bookings.edit', [
            'booking' => $booking,
            'clients' => Client::when($booking->hotel_id, fn ($query) => $query->associatedWithHotel((int) $booking->hotel_id))->orderBy('company_name')->get(),
            'rooms' => MeetingRoom::where('hotel_id', $booking->hotel_id)->orderBy('code')->get(),
            'packages' => MeetingPackage::where('hotel_id', $booking->hotel_id)->where('is_active', true)->with('entitlements')->orderBy('code')->get(),
            'sources' => StoreBookingRequest::SOURCES,
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking, AuditLogger $auditLogger, MeetingRoomConflictService $conflicts, MeetingStateTransition $stateTransition, MeetingQRService $meetingQRService)
    {
        $this->authorize('update', $booking);
        $validated = $request->validated();
        $meeting = $booking->meetingEvents()->oldest()->first();

        if ($this->hasMeetingPayload($validated)) {
            $conflict = $conflicts->findConflict((int) $booking->hotel_id, (int) $validated['meeting_room_id'], $validated['start_at'], $validated['end_at'], $meeting?->id);
            if ($conflict) {
                return back()->withInput()->withErrors(['meeting_room_id' => "Room is already assigned to {$conflict->event_name}."]);
            }
        }

        $before = $booking->toArray();
        DB::transaction(function () use ($booking, $validated, $request, $auditLogger, $before, $stateTransition, $meetingQRService) {
            $booking->update(array_merge(Arr::only($validated, ['external_booking_id', 'client_id', 'booking_number', 'booking_source', 'booking_date', 'status', 'notes']), ['updated_by' => $request->user()->id]));
            $meeting = $this->hasMeetingPayload($validated)
                ? $this->upsertPrimaryMeeting($booking->fresh(), $validated, $request->user()->id, $stateTransition)
                : $booking->meetingEvents()->oldest()->first();

            if ($meeting && $booking->status === BookingStatus::CONFIRMED && ! $meeting->meeting_qr_token_hash) {
                $meetingQRService->generate($meeting->fresh(), $request->user()->id);
            }

            $event = (int) ($before['client_id'] ?? 0) !== (int) $booking->client_id ? 'booking.client_changed' : 'booking.updated';
            $auditLogger->record($event, $booking->hotel_id, $request->user()->id, $booking, [], $before, $booking->fresh()->toArray());
        });

        return redirect()->route('bookings.show', $booking)->with('status', 'Booking updated.');
    }

    public function destroy(Request $request, Booking $booking, AuditLogger $auditLogger)
    {
        $this->authorize('delete', $booking);
        $before = $booking->toArray();
        $booking->update(['status' => BookingStatus::CANCELLED->value]);
        $auditLogger->record('booking.cancelled', $booking->hotel_id, $request->user()->id, $booking, [], $before, $booking->fresh()->toArray());

        return redirect()->route('bookings.index')->with('status', 'Booking cancelled.');
    }

    public function changeStatus(Request $request, Booking $booking, AuditLogger $auditLogger, MeetingQRService $meetingQRService, MeetingStateTransition $stateTransition)
    {
        $this->authorize('update', $booking);
        $validated = $request->validate(['status' => ['required', Rule::in([BookingStatus::CONFIRMED->value, BookingStatus::CANCELLED->value])]]);
        $before = $booking->toArray();

        DB::transaction(function () use ($booking, $validated, $request, $auditLogger, $meetingQRService, $stateTransition, $before) {
            $booking->update(['status' => $validated['status'], 'updated_by' => $request->user()->id]);

            foreach ($booking->meetingEvents()->get() as $meeting) {
                if ($validated['status'] === BookingStatus::CONFIRMED->value) {
                    $meeting->update(['status' => MeetingStatus::SCHEDULED->value]);
                    $stateTransition->syncRoomStatus($meeting->fresh(), MeetingStatus::SCHEDULED);
                    if (! $meeting->meeting_qr_token_hash || $meeting->meeting_qr_revoked_at) {
                        $meetingQRService->generate($meeting->fresh(), $request->user()->id);
                    }
                }

                if ($validated['status'] === BookingStatus::CANCELLED->value) {
                    $meeting->update(['status' => MeetingStatus::CANCELLED->value, 'cancelled_at' => now()]);
                    $stateTransition->syncRoomStatus($meeting->fresh(), MeetingStatus::CANCELLED);
                    if ($meeting->meeting_qr_token_hash && ! $meeting->meeting_qr_revoked_at) {
                        $meetingQRService->revoke($meeting->fresh(), $request->user()->id);
                    }
                }
            }

            $auditLogger->record('booking.status_changed', $booking->hotel_id, $request->user()->id, $booking, ['status' => $validated['status']], $before, $booking->fresh()->toArray());
        });

        return back()->with('status', 'Booking status updated.');
    }

    private function upsertPrimaryMeeting(Booking $booking, array $data, int $userId, MeetingStateTransition $stateTransition): MeetingEvent
    {
        $meetingStatus = ($booking->status === BookingStatus::CONFIRMED) ? MeetingStatus::SCHEDULED : MeetingStatus::DRAFT;
        $meeting = $booking->meetingEvents()->oldest()->first() ?: new MeetingEvent([
            'hotel_id' => $booking->hotel_id,
            'booking_id' => $booking->id,
            'actual_participants' => 0,
            'created_by' => $userId,
        ]);

        $meeting->fill([
            'meeting_room_id' => $data['meeting_room_id'],
            'event_name' => $data['event_name'],
            'event_date' => $data['event_date'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'expected_participants' => $data['expected_participants'],
            'status' => $meetingStatus->value,
            'updated_by' => $userId,
        ])->save();

        $package = MeetingPackage::findOrFail($data['package_id']);
        MeetingPackageAssignment::updateOrCreate(
            ['meeting_event_id' => $meeting->id],
            ['package_id' => $package->id, 'participant_quota' => $data['expected_participants'], 'unit_price' => $package->price]
        );
        $stateTransition->syncRoomStatus($meeting->fresh(), $meetingStatus);

        return $meeting->fresh();
    }

    private function hasMeetingPayload(array $data): bool
    {
        foreach (['event_name', 'event_date', 'start_at', 'end_at', 'meeting_room_id', 'package_id', 'expected_participants'] as $key) {
            if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                return false;
            }
        }

        return true;
    }

    private function nextBookingNumber(int $hotelId): string
    {
        DB::statement('SELECT pg_advisory_xact_lock(?)', [$hotelId + 710000]);
        $prefix = 'BKG-'.now()->format('Ymd').'-';
        $last = Booking::withoutGlobalScope('hotel')
            ->where('hotel_id', $hotelId)
            ->where('booking_number', 'like', $prefix.'%')
            ->orderByDesc('booking_number')
            ->value('booking_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        do {
            $number = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Booking::withoutGlobalScope('hotel')->where('hotel_id', $hotelId)->where('booking_number', $number)->exists());

        return $number;
    }
}
