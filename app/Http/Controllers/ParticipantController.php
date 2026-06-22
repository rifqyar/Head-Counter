<?php

namespace App\Http\Controllers;

use App\Actions\RegisterParticipantAction;
use App\Domain\Booking\Client;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Http\Requests\RegisterParticipantRequest;
use App\Http\Requests\UpdateParticipantRequest;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['meeting_event_id', 'client_id', 'meeting_date']);
        $participants = Participant::with(['meetingEvent.booking.client'])
            ->when($request->filled('meeting_event_id'), fn ($query) => $query->where('meeting_event_id', $request->integer('meeting_event_id')))
            ->when($request->filled('client_id'), function ($query) use ($request) {
                $query->whereHas('meetingEvent.booking', fn ($bookingQuery) => $bookingQuery->where('client_id', $request->integer('client_id')));
            })
            ->when($request->filled('meeting_date'), function ($query) use ($request) {
                $query->whereHas('meetingEvent', fn ($meetingQuery) => $meetingQuery->whereDate('event_date', $request->date('meeting_date')->toDateString()));
            })
            ->orderByDesc(
                MeetingEvent::select('start_at')
                    ->whereColumn('meeting_events.id', 'participants.meeting_event_id')
                    ->limit(1)
            )
            ->orderBy('full_name')
            ->paginate(25)
            ->withQueryString();

        $meetings = MeetingEvent::with('booking.client')->orderByDesc('start_at')->get();
        $clients = Client::orderBy('company_name')->get();

        return $request->wantsJson() ? response()->json($participants) : $this->viewOrRedirect($request, 'domain.participants.index', compact('participants', 'meetings', 'clients', 'filters'));
    }

    public function create(Request $request)
    {
        return $this->viewOrRedirect($request, 'domain.participants.create', [
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

    public function show(Request $request, Participant $participant)
    {
        $this->authorize('view', $participant);

        return $this->viewOrRedirect($request, 'domain.participants.show', compact('participant'));
    }

    public function edit(Request $request, Participant $participant)
    {
        $this->authorize('update', $participant);

        return $this->viewOrRedirect($request, 'domain.participants.edit', compact('participant'));
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
