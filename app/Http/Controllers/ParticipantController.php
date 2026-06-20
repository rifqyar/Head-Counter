<?php

namespace App\Http\Controllers;

use App\Actions\RegisterParticipantAction;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Http\Requests\RegisterParticipantRequest;
use App\Http\Requests\UpdateParticipantRequest;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $participants = Participant::with('meetingEvent')->orderByDesc('registered_at')->paginate(25);

        return $request->wantsJson() ? response()->json($participants) : view('domain.participants.index', compact('participants'));
    }

    public function create()
    {
        return view('domain.participants.create', [
            'participant' => new Participant,
            'meetings' => MeetingEvent::orderByDesc('start_at')->get(),
        ]);
    }

    public function store(RegisterParticipantRequest $request, RegisterParticipantAction $action)
    {
        $meeting = MeetingEvent::findOrFail($request->validated('meeting_event_id'));
        $participant = $action->execute($meeting, $request->validated());

        return redirect()->route('participants.show', $participant);
    }

    public function show(Participant $participant)
    {
        $this->authorize('view', $participant);

        return view('domain.participants.show', compact('participant'));
    }

    public function edit(Participant $participant)
    {
        $this->authorize('update', $participant);

        return view('domain.participants.edit', compact('participant'));
    }

    public function update(UpdateParticipantRequest $request, Participant $participant)
    {
        $this->authorize('update', $participant);
        $data = $request->validated();
        $data['normalized_email'] = ! empty($data['email']) ? mb_strtolower(trim($data['email'])) : null;
        $data['normalized_phone'] = ! empty($data['phone']) ? preg_replace('/[^0-9+]/', '', $data['phone']) : null;
        $participant->update($data);

        return redirect()->route('participants.show', $participant)->with('status', 'Participant updated.');
    }

    public function destroy(Participant $participant)
    {
        $this->authorize('delete', $participant);
        $participant->update(['status' => 'CANCELLED']);

        return redirect()->route('participants.index')->with('status', 'Participant cancelled.');
    }
}
