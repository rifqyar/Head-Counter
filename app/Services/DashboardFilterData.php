<?php

namespace App\Services;

use App\Domain\Booking\Client;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Enums\MeetingStatus;
use App\Models\User;
use App\Support\Reporting\ReportFilter;

class DashboardFilterData
{
    public function build(User $user, ReportFilter $filter): array
    {
        $hotelIds = $filter->hotelIds();

        return [
            'hotels' => $user->isSuperAdmin() ? Hotel::where('status', 'ACTIVE')->orderBy('name')->get() : collect(),
            'rooms' => MeetingRoom::query()
                ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
                ->orderBy('name')
                ->get(),
            'clients' => Client::query()
                ->when($hotelIds, fn ($query) => $query->whereHas('hotels', fn ($hotels) => $hotels->whereIn('hotels.id', $hotelIds)))
                ->orderBy('company_name')
                ->get(),
            'meetings' => MeetingEvent::query()
                ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
                ->orderByDesc('start_at')
                ->limit(100)
                ->get(),
            'statuses' => MeetingStatus::cases(),
        ];
    }
}
