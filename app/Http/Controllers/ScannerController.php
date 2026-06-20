<?php

namespace App\Http\Controllers;

use App\Actions\RedeemParticipantAction;
use App\Domain\Catering\MealSession;
use App\Http\Requests\RedeemParticipantRequest;
use App\Http\Requests\ValidateParticipantQRRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function page(Request $request)
    {
        $hotelId = $this->hotelId($request);
        $sessions = MealSession::where('hotel_id', $hotelId)->orderByDesc('starts_at')->orderBy('name')->get();

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
}
