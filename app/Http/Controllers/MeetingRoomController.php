<?php

namespace App\Http\Controllers;

use App\Domain\Meeting\MeetingRoom;
use App\Http\Requests\StoreMeetingRoomRequest;
use App\Http\Requests\UpdateMeetingRoomRequest;
use Illuminate\Http\Request;

class MeetingRoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = MeetingRoom::orderBy('code')->paginate(25);

        return $request->wantsJson() ? response()->json($rooms) : view('domain.meeting-rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('domain.meeting-rooms.create', ['room' => new MeetingRoom]);
    }

    public function store(StoreMeetingRoomRequest $request)
    {
        $room = MeetingRoom::create($request->validated());

        return redirect()->route('meeting-rooms.show', $room);
    }

    public function show(MeetingRoom $meetingRoom)
    {
        $this->authorize('view', $meetingRoom);

        return view('domain.meeting-rooms.show', ['room' => $meetingRoom]);
    }

    public function edit(MeetingRoom $meetingRoom)
    {
        $this->authorize('update', $meetingRoom);

        return view('domain.meeting-rooms.edit', ['room' => $meetingRoom]);
    }

    public function update(UpdateMeetingRoomRequest $request, MeetingRoom $meetingRoom)
    {
        $meetingRoom->update($request->validated());

        return redirect()->route('meeting-rooms.show', $meetingRoom);
    }

    public function destroy(MeetingRoom $meetingRoom)
    {
        $this->authorize('delete', $meetingRoom);
        $meetingRoom->update(['operational_status' => 'INACTIVE']);

        return redirect()->route('meeting-rooms.index');
    }
}
