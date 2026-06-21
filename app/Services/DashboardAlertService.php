<?php

namespace App\Services;

use App\Domain\Catering\MealSession;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Redemption\Redemption;
use App\Enums\MealSessionStatus;
use App\Enums\MeetingStatus;
use App\Enums\RedemptionStatus;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportFilter;
use Illuminate\Support\Facades\DB;

class DashboardAlertService
{
    public function __construct(private readonly HotelTimezoneService $timezones) {}

    public function build(ReportFilter $filter): array
    {
        $hotelIds = $filter->hotelIds();
        $now = $this->timezones->localNow($filter->hotel)->utc();
        $startingSoonEnd = $now->addMinutes((int) config('reports.starting_soon_minutes', 60));

        $base = MeetingEvent::query()
            ->with(['hotel', 'meetingRoom'])
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds));

        $startingSoon = (clone $base)
            ->whereBetween('start_at', [$now, $startingSoonEnd])
            ->whereIn('status', [MeetingStatus::SCHEDULED->value, MeetingStatus::CHECKIN_OPEN->value])
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        $runningLate = (clone $base)
            ->where('end_at', '<', $now)
            ->where('status', MeetingStatus::OCCUPIED->value)
            ->orderBy('end_at')
            ->limit(10)
            ->get();

        $overCapacity = (clone $base)
            ->leftJoinSub(
                DB::table('participants')
                    ->select('meeting_event_id', DB::raw('count(*) as active_participants_count'))
                    ->whereNotIn('status', ['CANCELLED', 'BLOCKED'])
                    ->groupBy('meeting_event_id'),
                'participant_counts',
                'participant_counts.meeting_event_id',
                '=',
                'meeting_events.id'
            )
            ->whereRaw('coalesce(participant_counts.active_participants_count, 0) > meeting_events.expected_participants')
            ->select('meeting_events.*')
            ->limit(10)
            ->get();

        $openMealSessions = MealSession::query()
            ->with(['hotel', 'meetingEvent'])
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->where('status', MealSessionStatus::OPEN->value)
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        $recentFailures = Redemption::query()
            ->with(['hotel', 'participant', 'meetingEvent', 'mealSession'])
            ->when($hotelIds, fn ($query) => $query->withoutGlobalScope('hotel')->whereIn('hotel_id', $hotelIds))
            ->where('status', RedemptionStatus::REJECTED->value)
            ->where('created_at', '>=', $now->subHours((int) config('reports.recent_scanner_failure_hours', 24)))
            ->latest('created_at')
            ->limit(10)
            ->get();

        $roomConflicts = DB::table('meeting_events as a')
            ->join('meeting_events as b', function ($join) {
                $join->on('a.meeting_room_id', '=', 'b.meeting_room_id')
                    ->whereColumn('a.id', '<', 'b.id')
                    ->whereColumn('a.start_at', '<', 'b.end_at')
                    ->whereColumn('b.start_at', '<', 'a.end_at');
            })
            ->when($hotelIds, fn ($query) => $query->whereIn('a.hotel_id', $hotelIds))
            ->whereNotNull('a.meeting_room_id')
            ->whereNotIn('a.status', ['CANCELLED', 'NO_SHOW'])
            ->whereNotIn('b.status', ['CANCELLED', 'NO_SHOW'])
            ->select('a.event_name as first_event', 'b.event_name as second_event')
            ->limit(10)
            ->get();

        return compact('startingSoon', 'runningLate', 'roomConflicts', 'overCapacity', 'openMealSessions', 'recentFailures');
    }
}
