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
        $meetings = MeetingEvent::with('meetingRoom')->orderByDesc('start_at')->paginate(25);

        return $request->wantsJson() ? response()->json($meetings) : view('domain.meetings.index', compact('meetings'));
    }

    public function create()
    {
        return view('domain.meetings.create', [
            'meeting' => new MeetingEvent,
            'bookings' => Booking::orderByDesc('booking_date')->get(),
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

    public function show(MeetingEvent $meeting)
    {
        $this->authorize('view', $meeting);

        return view('domain.meetings.show', compact('meeting'));
    }

    public function edit(MeetingEvent $meeting)
    {
        $this->authorize('update', $meeting);

        return view('domain.meetings.edit', [
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
