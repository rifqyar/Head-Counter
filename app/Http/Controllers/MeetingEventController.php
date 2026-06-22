<?php

namespace App\Http\Controllers;

use App\Actions\CreateMeetingEventAction;
use App\Actions\TransitionMeetingStatusAction;
use App\Actions\UpdateMeetingEventAction;
use App\Domain\Booking\Booking;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Exceptions\DomainException;
use App\Http\Requests\StoreMeetingEventRequest;
use App\Http\Requests\TransitionMeetingStatusRequest;
use App\Http\Requests\UpdateMeetingEventRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class MeetingEventController extends Controller
{
    public function index(Request $request)
    {
        $query = MeetingEvent::with(['meetingRoom', 'booking.client'])
            ->orderByDesc('start_at');

        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date('date')->toDateString());
        }

        if ($request->filled('client')) {
            $client = trim((string) $request->input('client'));
            $query->whereHas('booking.client', function ($clientQuery) use ($client) {
                $clientQuery
                    ->where('company_name', 'like', '%'.$client.'%')
                    ->orWhere('external_id', 'like', '%'.$client.'%')
                    ->orWhere('contact_name', 'like', '%'.$client.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $meetings = $query->paginate(25)->withQueryString();
        $filters = $request->only(['date', 'client', 'status']);

        return $request->wantsJson() ? response()->json($meetings) : $this->viewOrRedirect($request, 'domain.meetings.index', compact('meetings', 'filters'));
    }

    public function create(Request $request)
    {
        return $this->viewOrRedirect($request, 'domain.meetings.create', [
            'meeting' => new MeetingEvent,
            'bookings' => Booking::with(['client', 'meetingEvents.meetingRoom', 'meetingEvents.packageAssignments.package'])
                ->whereHas('meetingEvents', fn ($query) => $query->where('status', 'DRAFT'))
                ->orderByDesc('booking_date')
                ->get(),
            'rooms' => MeetingRoom::orderBy('code')->get(),
            'packages' => MeetingPackage::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(StoreMeetingEventRequest $request, CreateMeetingEventAction $action)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        abort_if($hotelId === null, 422, 'Select a hotel context before creating a meeting.');

        $meeting = $action->execute(array_merge($request->validated(), ['hotel_id' => $hotelId]));

        return redirect()->route('meetings.show', $meeting)->with('status', 'Meeting created.');
    }

    public function show(Request $request, MeetingEvent $meeting)
    {
        $this->authorize('view', $meeting);
        $meeting->load([
            'booking.client',
            'meetingRoom',
            'participants.activeQrCredential',
            'attendances.participant',
            'packageAssignments.package.entitlements',
        ]);

        return $this->viewOrRedirect($request, 'domain.meetings.show', compact('meeting'));
    }

    public function edit(Request $request, MeetingEvent $meeting)
    {
        $this->authorize('update', $meeting);

        return $this->viewOrRedirect($request, 'domain.meetings.edit', [
            'meeting' => $meeting,
            'bookings' => Booking::orderByDesc('booking_date')->get(),
            'rooms' => MeetingRoom::orderBy('code')->get(),
            'packages' => MeetingPackage::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function update(UpdateMeetingEventRequest $request, MeetingEvent $meeting, UpdateMeetingEventAction $action)
    {
        $action->execute($meeting, $request->validated());

        return redirect()->route('meetings.show', $meeting)->with('status', 'Meeting updated.');
    }

    public function transition(TransitionMeetingStatusRequest $request, MeetingEvent $meeting, TransitionMeetingStatusAction $action)
    {
        $this->authorize('transition', $meeting);
        try {
            $action->execute($meeting, $request->validated('status'));
        } catch (DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('meetings.show', $meeting)->with('status', 'Meeting status updated.');
    }

    public function destroy(MeetingEvent $meeting, TransitionMeetingStatusAction $action)
    {
        $this->authorize('delete', $meeting);
        try {
            $action->execute($meeting, 'CANCELLED');
        } catch (DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return redirect()->route('meetings.index')->with('status', 'Meeting cancelled.');
    }
}
