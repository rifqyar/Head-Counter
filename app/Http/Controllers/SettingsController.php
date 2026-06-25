<?php

namespace App\Http\Controllers;

use App\Domain\Hotel\Hotel;
use App\Http\Requests\UpdateTenantSettingsRequest;
use App\Support\Audit\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('settings.manage'), 403);

        $currentHotel = $this->currentHotel($request);
        $hotels = $request->user()->isSuperAdmin()
            ? Hotel::withCount('users')->orderBy('name')->get()
            : collect();

        return $this->viewOrRedirect($request, 'domain.settings.index', [
            'currentHotel' => $currentHotel,
            'hotels' => $hotels,
        ]);
    }

    public function update(UpdateTenantSettingsRequest $request, AuditLogger $auditLogger)
    {
        $hotel = $this->hotelForUpdate($request);
        abort_unless($hotel, 404);

        $before = [
            'name' => $hotel->name,
            'address' => $hotel->address,
            'timezone' => $hotel->timezone,
            'settings' => $hotel->settings ?? [],
        ];

        $data = $request->validated();
        $settings = array_merge($hotel->settings ?? [], $data['settings'] ?? []);
        unset($data['hotel_id'], $data['settings']);

        DB::transaction(function () use ($hotel, $data, $settings, $auditLogger, $request, $before) {
            $hotel->update(array_merge($data, ['settings' => $settings]));
            $hotel->refresh();

            $auditLogger->record(
                'settings.updated',
                $hotel->id,
                $request->user()->id,
                $hotel,
                [],
                $before,
                [
                    'name' => $hotel->name,
                    'address' => $hotel->address,
                    'timezone' => $hotel->timezone,
                    'settings' => $hotel->settings ?? [],
                ],
            );
        });

        return redirect()->route('settings.index')->with('status', 'Settings updated.');
    }

    private function currentHotel(Request $request): ?Hotel
    {
        $tenantHotel = app(TenantContext::class)->hotel();
        if ($tenantHotel) {
            return $tenantHotel;
        }

        if (! $request->user()->isSuperAdmin() && $request->user()->hotel_id) {
            return Hotel::find($request->user()->hotel_id);
        }

        return null;
    }

    private function hotelForUpdate(UpdateTenantSettingsRequest $request): ?Hotel
    {
        if ($request->user()->isSuperAdmin() && $request->filled('hotel_id')) {
            return Hotel::whereKey($request->integer('hotel_id'))->first();
        }

        return $this->currentHotel($request);
    }
}
