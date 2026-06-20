<?php

namespace App\Http\Controllers;

use App\Domain\Catering\MealSession;
use App\Domain\Catering\MealSessionService;
use App\Domain\Meeting\MeetingEvent;
use App\Enums\EntitlementType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MealSessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = MealSession::with('meetingEvent')->orderByDesc('starts_at')->paginate(25);

        return $this->viewOrRedirect($request, 'domain.meal-sessions.index', compact('sessions'));
    }

    public function create(Request $request)
    {
        return $this->viewOrRedirect($request, 'domain.meal-sessions.create', ['session' => new MealSession, 'meetings' => MeetingEvent::orderByDesc('start_at')->get(), 'types' => EntitlementType::cases()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $meeting = MeetingEvent::findOrFail($data['meeting_event_id']);
        MealSession::create(array_merge($data, ['hotel_id' => $meeting->hotel_id, 'created_by' => $request->user()->id]));

        return redirect()->route('meal-sessions.index')->with('status', 'Meal session created.');
    }

    public function edit(Request $request, MealSession $mealSession)
    {
        return $this->viewOrRedirect($request, 'domain.meal-sessions.edit', ['session' => $mealSession, 'meetings' => MeetingEvent::orderByDesc('start_at')->get(), 'types' => EntitlementType::cases()]);
    }

    public function update(Request $request, MealSession $mealSession)
    {
        $mealSession->update(array_merge($this->validated($request), ['updated_by' => $request->user()->id]));

        return redirect()->route('meal-sessions.index')->with('status', 'Meal session updated.');
    }

    public function open(MealSession $mealSession, MealSessionService $service)
    {
        $service->open($mealSession, auth()->id());

        return back()->with('status', 'Meal session opened.');
    }

    public function close(MealSession $mealSession, MealSessionService $service)
    {
        $service->close($mealSession, auth()->id());

        return back()->with('status', 'Meal session closed.');
    }

    public function cancel(MealSession $mealSession, MealSessionService $service)
    {
        $service->cancel($mealSession, auth()->id());

        return back()->with('status', 'Meal session cancelled.');
    }

    public function generate(MeetingEvent $meeting, MealSessionService $service)
    {
        $service->generateFromPackages($meeting, auth()->id());

        return back()->with('status', 'Meal sessions generated from package entitlements.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'meeting_event_id' => ['required', Rule::exists('meeting_events', 'id')],
            'entitlement_type' => ['required', Rule::enum(EntitlementType::class)],
            'session_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', 'in:DRAFT,OPEN,CLOSED,CANCELLED'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
