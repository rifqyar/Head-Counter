<?php

namespace App\Http\Controllers;

use App\Actions\OverrideRedemptionAction;
use App\Actions\RedeemParticipantAction;
use App\Actions\ReverseRedemptionAction;
use App\Domain\Redemption\Redemption;
use App\Http\Requests\OverrideRedemptionRequest;
use App\Http\Requests\ReverseRedemptionRequest;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Redemption::class);

        $query = Redemption::with(['participant', 'meetingEvent', 'mealSession', 'originalRedemption'])->orderByDesc('created_at');

        foreach (['hotel_id', 'meeting_event_id', 'participant_id', 'meal_session_id', 'rejection_code', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $redemptions = $query->paginate(25)->withQueryString();
        $overrideableCodes = RedeemParticipantAction::OVERRIDEABLE_REJECTION_CODES;

        return $this->viewOrRedirect($request, 'domain.redemptions.index', compact('redemptions', 'overrideableCodes'));
    }

    public function show(Request $request, Redemption $redemption)
    {
        $this->authorize('view', $redemption);

        $redemption->load(['participant', 'meetingEvent', 'mealSession', 'participantEntitlement', 'originalRedemption', 'overrideRedemptions']);
        $overrideableCodes = RedeemParticipantAction::OVERRIDEABLE_REJECTION_CODES;

        return $this->viewOrRedirect($request, 'domain.redemptions.show', compact('redemption', 'overrideableCodes'));
    }

    public function override(OverrideRedemptionRequest $request, Redemption $redemption, OverrideRedemptionAction $action)
    {
        $this->authorize('override', $redemption);

        $data = $request->validated();
        try {
            $action->execute($redemption, $request->user()->id, $data['reason']);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['reason' => $exception->getMessage()]);
        }

        return back()->with('status', 'Redemption overridden.');
    }

    public function reverse(ReverseRedemptionRequest $request, Redemption $redemption, ReverseRedemptionAction $action)
    {
        $this->authorize('reverse', $redemption);

        $data = $request->validated();
        $action->execute($redemption, $request->user()->id, $data['reason']);

        return back()->with('status', 'Redemption reversed.');
    }
}
