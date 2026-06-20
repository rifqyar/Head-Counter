<?php

namespace App\Http\Controllers;

use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingRoom;
use App\Http\Requests\StoreMeetingRoomRequest;
use App\Http\Requests\UpdateMeetingRoomRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class MeetingRoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = MeetingRoom::with('hotel')->orderBy('code')->paginate(25);

        return $request->wantsJson() ? response()->json($rooms) : $this->viewOrRedirect($request, 'domain.meeting-rooms.index', compact('rooms'));
    }

    public function create(Request $request)
    {
        return $this->viewOrRedirect($request, 'domain.meeting-rooms.create', [
            'room' => new MeetingRoom,
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'currentHotel' => app(TenantContext::class)->hotel(),
        ]);
    }

    public function store(StoreMeetingRoomRequest $request)
    {
        $data = $request->validated();
        $data['hotel_id'] = $request->targetHotelId();
        abort_if($data['hotel_id'] === null, 422, 'Select a hotel context before creating a meeting room.');

        $room = MeetingRoom::create($data);

        return redirect()->route('meeting-rooms.show', $room)->with('status', 'Meeting room created.');
    }

    public function show(Request $request, MeetingRoom $meetingRoom)
    {
        $this->authorize('view', $meetingRoom);

        return $this->viewOrRedirect($request, 'domain.meeting-rooms.show', ['room' => $meetingRoom->load('hotel')->loadCount('meetings')]);
    }

    public function edit(Request $request, MeetingRoom $meetingRoom)
    {
        $this->authorize('update', $meetingRoom);

        return $this->viewOrRedirect($request, 'domain.meeting-rooms.edit', [
            'room' => $meetingRoom->load('hotel'),
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'currentHotel' => app(TenantContext::class)->hotel(),
        ]);
    }

    public function update(UpdateMeetingRoomRequest $request, MeetingRoom $meetingRoom)
    {
        $data = $request->validated();
        $data['hotel_id'] = $request->targetHotelId();
        $meetingRoom->update($data);

        return redirect()->route('meeting-rooms.show', $meetingRoom)->with('status', 'Meeting room updated.');
    }

    public function destroy(MeetingRoom $meetingRoom)
    {
        $this->authorize('delete', $meetingRoom);
        $meetingRoom->update(['operational_status' => 'INACTIVE']);

        return redirect()->route('meeting-rooms.index')->with('status', 'Meeting room deactivated.');
    }
}
