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
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        $sessions = MealSession::where('hotel_id', $hotelId)->orderByDesc('starts_at')->orderBy('name')->get();

        return view('domain.scanner.index', compact('sessions'));
    }

    public function validateQr(ValidateParticipantQRRequest $request, RedeemParticipantAction $action)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;

        return response()->json($action->validateOnly($request->validated(), $hotelId));
    }

    public function redeem(RedeemParticipantRequest $request, RedeemParticipantAction $action)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        $result = $action->execute($request->validated(), $request->user()->id, $hotelId);

        return response()->json($result['body'], $result['status']);
    }
}
