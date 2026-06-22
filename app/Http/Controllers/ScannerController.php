<?php

namespace App\Http\Controllers;

use App\Actions\RedeemParticipantAction;
use App\Domain\Catering\MealSession;
use App\Domain\Catering\MealSessionService;
use App\Domain\Meeting\MeetingEvent;
use App\Enums\MealSessionStatus;
use App\Http\Requests\RedeemParticipantRequest;
use App\Http\Requests\ValidateParticipantQRRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function page(Request $request, MealSessionService $mealSessionService)
    {
        $hotelId = $this->hotelId($request);
        $this->ensureScannerSessions($hotelId, $request->user()->id, $mealSessionService);

        $sessions = MealSession::with('meetingEvent')
            ->where('hotel_id', $hotelId)
            ->where('status', '!=', MealSessionStatus::CANCELLED->value)
            ->orderByDesc('starts_at')
            ->orderBy('name')
            ->get();

        return $this->viewOrRedirect($request, 'domain.scanner.index', compact('sessions'));
    }

    public function validateQr(ValidateParticipantQRRequest $request, RedeemParticipantAction $action)
    {
        $hotelId = $this->hotelId($request);
        $result = $action->validateOnly($request->validated(), $hotelId);

        return response()->json($result, $result['eligible'] ? 200 : 422);
    }

    public function redeem(RedeemParticipantRequest $request, RedeemParticipantAction $action)
    {
        $hotelId = $this->hotelId($request);
        $result = $action->execute($request->validated(), $request->user()->id, $hotelId);

        return response()->json($result['body'], $result['status']);
    }

    private function hotelId(Request $request): int
    {
        $hotelId = $request->user()->isSuperAdmin()
            ? app(TenantContext::class)->hotelId()
            : $request->user()->hotel_id;

        abort_if($hotelId === null, 403, 'Select an active hotel context.');

        return (int) $hotelId;
    }

    private function ensureScannerSessions(int $hotelId, int $actorId, MealSessionService $mealSessionService): void
    {
        MeetingEvent::with(['packageAssignments.package.entitlements', 'mealSessions'])
            ->where('hotel_id', $hotelId)
            ->whereHas('packageAssignments.package.entitlements')
            ->whereDoesntHave('mealSessions')
            ->get()
            ->each(fn (MeetingEvent $meeting) => $mealSessionService->generateFromPackages($meeting, $actorId, MealSessionStatus::OPEN));
    }
}
