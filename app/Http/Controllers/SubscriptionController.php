<?php

namespace App\Http\Controllers;

use App\Domain\Hotel\Hotel;
use App\Http\Requests\UpdateHotelSubscriptionRequest;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $hotels = Hotel::withCount('users')
            ->orderBy('name')
            ->paginate(25);

        return $this->viewOrRedirect($request, 'domain.settings.subscriptions', compact('hotels'));
    }

    public function update(UpdateHotelSubscriptionRequest $request, Hotel $hotel, AuditLogger $auditLogger)
    {
        $data = $request->validated();
        $before = $hotel->settings['subscription'] ?? [];

        DB::transaction(function () use ($hotel, $data, $before, $auditLogger, $request) {
            $settings = $hotel->settings ?? [];
            $settings['subscription'] = $data;
            $hotel->update(['settings' => $settings]);

            $auditLogger->record(
                'hotel.subscription_updated',
                $hotel->id,
                $request->user()->id,
                $hotel,
                [],
                ['subscription' => $before],
                ['subscription' => $data],
            );
        });

        return redirect()->route('settings.subscriptions.index')->with('status', 'Subscription updated.');
    }
}
