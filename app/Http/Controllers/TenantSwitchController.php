<?php

namespace App\Http\Controllers;

use App\Domain\Hotel\Hotel;
use Illuminate\Http\Request;

class TenantSwitchController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return $this->viewOrRedirect($request, 'domain.tenancy.switcher', [
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'currentHotel' => $request->session()->has('tenant_hotel_id')
                ? Hotel::find($request->session()->get('tenant_hotel_id'))
                : null,
        ]);
    }

    public function switch(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
        ]);

        $hotel = Hotel::where('status', 'ACTIVE')->find($validated['hotel_id']);
        if (! $hotel) {
            return back()->withErrors(['hotel_id' => 'Select an active hotel. The previous tenant context was kept.']);
        }

        $request->session()->put('tenant_hotel_id', $hotel->id);
        $request->session()->forget(['booking_filters', 'meeting_filters', 'client_filters']);

        return redirect()->route('dashboard')->with('status', 'Active hotel switched to '.$hotel->name.'.');
    }

    public function reset(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $request->session()->forget('tenant_hotel_id');

        return redirect()->route('dashboard')->with('status', 'Tenant context reset. Super-admin can view all tenant data.');
    }
}
