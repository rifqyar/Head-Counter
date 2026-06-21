<?php

namespace App\Services;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\Participant\Participant;
use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\Redemption;
use App\Enums\AttendanceType;
use App\Enums\EntitlementType;
use App\Enums\RedemptionStatus;
use App\Enums\RoomOperationalStatus;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportFilter;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    public function __construct(private readonly HotelTimezoneService $timezones) {}

    public function build(ReportFilter $filter): array
    {
        [$start, $end] = $this->timezones->rangeBounds($filter->get('date'), $filter->get('date'), $filter->hotel);
        $hotelIds = $filter->hotelIds();

        $meetingsQuery = MeetingEvent::query()
            ->with(['hotel', 'meetingRoom', 'booking.client'])
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->when($filter->get('room_id'), fn ($query, $id) => $query->where('meeting_room_id', $id))
            ->when($filter->get('client_id'), fn ($query, $id) => $query->whereHas('booking', fn ($booking) => $booking->where('client_id', $id)))
            ->when($filter->get('meeting_id'), fn ($query, $id) => $query->whereKey($id))
            ->when($filter->get('status'), fn ($query, $status) => $query->where('status', $status))
            ->whereBetween('start_at', [$start, $end]);

        $meetingsToday = (clone $meetingsQuery)->orderBy('start_at')->limit(10)->get();
        $meetingIds = (clone $meetingsQuery)->pluck('id');

        $expected = (int) (clone $meetingsQuery)->sum('expected_participants');
        $registered = Participant::query()
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->whereIn('meeting_event_id', $meetingIds)
            ->where('status', '!=', 'CANCELLED')
            ->count();
        $checkedIn = DB::table('meeting_attendances')
            ->whereIn('meeting_event_id', $meetingIds)
            ->where('attendance_type', AttendanceType::MEETING_CHECKIN->value)
            ->distinct('participant_id')
            ->count('participant_id');

        $roomCounts = MeetingRoom::query()
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->select('operational_status', DB::raw('count(*) as aggregate'))
            ->groupBy('operational_status')
            ->pluck('aggregate', 'operational_status')
            ->all();

        $entitlements = ParticipantEntitlement::query()
            ->whereIn('meeting_event_id', $meetingIds)
            ->whereIn('entitlement_type', [EntitlementType::COFFEE_BREAK->value, EntitlementType::LUNCH->value])
            ->select('entitlement_type', DB::raw('sum(total_quantity) as total'), DB::raw('sum(redeemed_quantity) as redeemed'))
            ->groupBy('entitlement_type')
            ->get()
            ->keyBy(fn ($row) => $row->entitlement_type->value ?? $row->entitlement_type);

        $successfulRedemptions = Redemption::query()
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->whereIn('meeting_event_id', $meetingIds)
            ->whereIn('status', [RedemptionStatus::SUCCESS->value, RedemptionStatus::OVERRIDDEN->value])
            ->count();
        $rejectedScans = Redemption::query()
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->whereIn('meeting_event_id', $meetingIds)
            ->where('status', RedemptionStatus::REJECTED->value)
            ->count();

        $now = $this->timezones->localNow($filter->hotel)->utc();
        $upcomingEnd = $now->addHours((int) config('reports.upcoming_hours', 24));
        $upcomingMeetings = MeetingEvent::query()
            ->with(['hotel', 'meetingRoom', 'booking.client'])
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->whereBetween('start_at', [$now, $upcomingEnd])
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        return [
            'hotel' => $filter->hotel,
            'timezone' => $filter->timezone,
            'date' => $this->timezones->display($start, $filter->hotel, 'Y-m-d'),
            'meetings_today_count' => (clone $meetingsQuery)->count(),
            'meetings_today' => $meetingsToday,
            'upcoming_meetings' => $upcomingMeetings,
            'room_counts' => collect(RoomOperationalStatus::cases())->mapWithKeys(fn ($status) => [$status->value => (int) ($roomCounts[$status->value] ?? 0)])->all(),
            'expected_participants' => $expected,
            'registered_participants' => $registered,
            'checked_in_participants' => $checkedIn,
            'attendance_percentage' => $expected > 0 ? round(($checkedIn / $expected) * 100, 2) : 0,
            'redemption_summary' => [
                'coffee_break_total' => (int) ($entitlements[EntitlementType::COFFEE_BREAK->value]->total ?? 0),
                'coffee_break_redeemed' => (int) ($entitlements[EntitlementType::COFFEE_BREAK->value]->redeemed ?? 0),
                'lunch_total' => (int) ($entitlements[EntitlementType::LUNCH->value]->total ?? 0),
                'lunch_redeemed' => (int) ($entitlements[EntitlementType::LUNCH->value]->redeemed ?? 0),
                'successful_redemptions' => $successfulRedemptions,
                'rejected_scans' => $rejectedScans,
            ],
        ];
    }
}
