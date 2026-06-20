<?php

namespace App\Http\Controllers;

use App\Domain\Catering\MealSession;
use App\Domain\Catering\MealSessionService;
use App\Domain\Meeting\MeetingEvent;
use App\Enums\EntitlementType;
use App\Http\Requests\MealSessionRequest;
use Illuminate\Http\Request;

class MealSessionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', MealSession::class);

        $sessions = MealSession::with('meetingEvent')->orderByDesc('starts_at')->paginate(25);

        return $this->viewOrRedirect($request, 'domain.meal-sessions.index', compact('sessions'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', MealSession::class);

        return $this->viewOrRedirect($request, 'domain.meal-sessions.create', ['session' => new MealSession, 'meetings' => MeetingEvent::orderByDesc('start_at')->get(), 'types' => EntitlementType::cases()]);
    }

    public function store(MealSessionRequest $request)
    {
        $this->authorize('create', MealSession::class);

        $data = $request->validated();
        $meeting = MeetingEvent::findOrFail($data['meeting_event_id']);
        MealSession::create(array_merge($data, ['hotel_id' => $meeting->hotel_id, 'created_by' => $request->user()->id]));

        return redirect()->route('meal-sessions.index')->with('status', 'Meal session created.');
    }

    public function edit(Request $request, MealSession $mealSession)
    {
        $this->authorize('update', $mealSession);

        return $this->viewOrRedirect($request, 'domain.meal-sessions.edit', ['session' => $mealSession, 'meetings' => MeetingEvent::orderByDesc('start_at')->get(), 'types' => EntitlementType::cases()]);
    }

    public function update(MealSessionRequest $request, MealSession $mealSession)
    {
        $this->authorize('update', $mealSession);

        $mealSession->update(array_merge($request->validated(), ['updated_by' => $request->user()->id]));

        return redirect()->route('meal-sessions.index')->with('status', 'Meal session updated.');
    }

    public function open(MealSession $mealSession, MealSessionService $service)
    {
        $this->authorize('update', $mealSession);

        $service->open($mealSession, auth()->id());

        return back()->with('status', 'Meal session opened.');
    }

    public function close(MealSession $mealSession, MealSessionService $service)
    {
        $this->authorize('update', $mealSession);

        $service->close($mealSession, auth()->id());

        return back()->with('status', 'Meal session closed.');
    }

    public function cancel(MealSession $mealSession, MealSessionService $service)
    {
        $this->authorize('update', $mealSession);

        $service->cancel($mealSession, auth()->id());

        return back()->with('status', 'Meal session cancelled.');
    }

    public function generate(MeetingEvent $meeting, MealSessionService $service)
    {
        $this->authorize('update', $meeting);

        $service->generateFromPackages($meeting, auth()->id());

        return back()->with('status', 'Meal sessions generated from package entitlements.');
    }
}
