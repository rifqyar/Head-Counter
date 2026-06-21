<?php

namespace App\Services;

use App\Enums\AttendanceType;
use App\Enums\ReportType;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportCatalog;
use App\Support\Reporting\ReportFilter;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportQueryService
{
    public function __construct(private readonly HotelTimezoneService $timezones) {}

    public function count(string $type, ReportFilter $filter): int
    {
        return $this->query($type, $filter)->count();
    }

    public function rows(string $type, ReportFilter $filter, ?int $limit = null): Collection
    {
        $rows = $this->query($type, $filter);
        if ($limit) {
            $rows->limit($limit);
        }

        $hotels = DB::table('hotels')->pluck('timezone', 'id');

        return $rows->get()->map(fn ($row) => $this->formatRow($type, (array) $row, $filter, $hotels));
    }

    public function headings(string $type, ReportFilter $filter): array
    {
        return ReportCatalog::columns($type, $filter->hotel === null, $filter->user->can('participant.contact.view') || $filter->user->can('participant.view') || $filter->user->isSuperAdmin());
    }

    public function query(string $type, ReportFilter $filter): Builder
    {
        return match ($type) {
            ReportType::MEETINGS->value => $this->meetingReport($filter),
            ReportType::PARTICIPANTS->value => $this->participantReport($filter),
            ReportType::REDEMPTIONS->value => $this->redemptionReport($filter),
            ReportType::PACKAGE_CONSUMPTION->value => $this->packageConsumptionReport($filter),
            ReportType::ROOM_UTILIZATION->value => $this->roomUtilizationReport($filter),
            default => abort(404),
        };
    }

    private function meetingReport(ReportFilter $filter): Builder
    {
        [$from, $to] = $this->timezones->rangeBounds($filter->get('date_from'), $filter->get('date_to'), $filter->hotel);

        return DB::table('meeting_events as m')
            ->leftJoin('hotels as h', 'h.id', '=', 'm.hotel_id')
            ->leftJoin('meeting_rooms as r', 'r.id', '=', 'm.meeting_room_id')
            ->leftJoin('bookings as b', 'b.id', '=', 'm.booking_id')
            ->leftJoin('clients as c', 'c.id', '=', 'b.client_id')
            ->leftJoin('meeting_package_assignments as a', 'a.meeting_event_id', '=', 'm.id')
            ->leftJoin('meeting_packages as p', 'p.id', '=', 'a.package_id')
            ->leftJoinSub(
                DB::table('meeting_attendances')->select('meeting_event_id', DB::raw('count(distinct participant_id) as checked_in'))->where('attendance_type', AttendanceType::MEETING_CHECKIN->value)->groupBy('meeting_event_id'),
                'att',
                'att.meeting_event_id',
                '=',
                'm.id'
            )
            ->select('m.*', 'h.name as hotel_name', 'h.timezone as hotel_timezone', 'r.name as room_name', 'b.booking_number', 'c.company_name as client_name', DB::raw("string_agg(distinct p.name, ', ') as package_names"), DB::raw('coalesce(att.checked_in, 0) as actual_count'))
            ->whereBetween('m.start_at', [$from, $to])
            ->when($filter->hotelIds(), fn ($query, $ids) => $query->whereIn('m.hotel_id', $ids))
            ->when($filter->get('room_id'), fn ($query, $id) => $query->where('m.meeting_room_id', $id))
            ->when($filter->get('client_id'), fn ($query, $id) => $query->where('b.client_id', $id))
            ->when($filter->get('meeting_id'), fn ($query, $id) => $query->where('m.id', $id))
            ->when($filter->get('status'), fn ($query, $status) => $query->where('m.status', $status))
            ->groupBy('m.id', 'h.name', 'h.timezone', 'r.name', 'b.booking_number', 'c.company_name', 'att.checked_in')
            ->orderBy('m.start_at');
    }

    private function participantReport(ReportFilter $filter): Builder
    {
        [$from, $to] = $this->timezones->rangeBounds($filter->get('date_from'), $filter->get('date_to'), $filter->hotel);

        return DB::table('participants as p')
            ->join('meeting_events as m', 'm.id', '=', 'p.meeting_event_id')
            ->leftJoin('hotels as h', 'h.id', '=', 'p.hotel_id')
            ->leftJoin('participant_qr_credentials as qr', function ($join) {
                $join->on('qr.participant_id', '=', 'p.id')->where('qr.status', '=', 'ACTIVE');
            })
            ->select('p.*', 'm.event_name', 'h.name as hotel_name', 'h.timezone as hotel_timezone', 'qr.status as qr_status')
            ->whereBetween('m.start_at', [$from, $to])
            ->when($filter->hotelIds(), fn ($query, $ids) => $query->whereIn('p.hotel_id', $ids))
            ->when($filter->get('meeting_id'), fn ($query, $id) => $query->where('p.meeting_event_id', $id))
            ->when($filter->get('attendance_status'), fn ($query, $status) => $status === 'CHECKED_IN' ? $query->whereNotNull('p.checked_in_at') : $query->whereNull('p.checked_in_at'))
            ->when($filter->get('qr_status'), fn ($query, $status) => $query->where('qr.status', $status))
            ->orderBy('m.start_at')
            ->orderBy('p.full_name');
    }

    private function redemptionReport(ReportFilter $filter): Builder
    {
        [$from, $to] = $this->timezones->rangeBounds($filter->get('date_from'), $filter->get('date_to'), $filter->hotel);

        return DB::table('redemptions as r')
            ->join('participants as p', 'p.id', '=', 'r.participant_id')
            ->join('meeting_events as m', 'm.id', '=', 'r.meeting_event_id')
            ->join('meal_sessions as s', 's.id', '=', 'r.meal_session_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.scanned_by')
            ->leftJoin('hotels as h', 'h.id', '=', 'r.hotel_id')
            ->leftJoin('redemptions as o', 'o.original_redemption_id', '=', 'r.id')
            ->select('r.*', 'p.full_name', 'm.event_name', 's.name as session_name', 's.entitlement_type', 'u.name as scanner_name', 'h.name as hotel_name', 'h.timezone as hotel_timezone', 'o.id as override_id')
            ->whereBetween(DB::raw('coalesce(r.redeemed_at, r.created_at)'), [$from, $to])
            ->when($filter->hotelIds(), fn ($query, $ids) => $query->whereIn('r.hotel_id', $ids))
            ->when($filter->get('meeting_id'), fn ($query, $id) => $query->where('r.meeting_event_id', $id))
            ->when($filter->get('meal_session_id'), fn ($query, $id) => $query->where('r.meal_session_id', $id))
            ->when($filter->get('status'), fn ($query, $status) => $query->where('r.status', $status))
            ->when($filter->get('rejection_code'), fn ($query, $code) => $query->where('r.rejection_code', $code))
            ->when($filter->get('scanner_id'), fn ($query, $id) => $query->where('r.scanned_by', $id))
            ->when($filter->get('entitlement_type'), fn ($query, $type) => $query->where('s.entitlement_type', $type))
            ->orderByDesc(DB::raw('coalesce(r.redeemed_at, r.created_at)'));
    }

    private function packageConsumptionReport(ReportFilter $filter): Builder
    {
        [$from, $to] = $this->timezones->rangeBounds($filter->get('date_from'), $filter->get('date_to'), $filter->hotel);

        return DB::table('meeting_events as m')
            ->leftJoin('hotels as h', 'h.id', '=', 'm.hotel_id')
            ->leftJoin('meeting_package_assignments as a', 'a.meeting_event_id', '=', 'm.id')
            ->leftJoin('meeting_packages as p', 'p.id', '=', 'a.package_id')
            ->leftJoinSub(DB::table('participants')->select('meeting_event_id', DB::raw('count(*) as registered'))->whereNotIn('status', ['CANCELLED', 'BLOCKED'])->groupBy('meeting_event_id'), 'part', 'part.meeting_event_id', '=', 'm.id')
            ->leftJoinSub(DB::table('participant_entitlements')->select('meeting_event_id', DB::raw('sum(total_quantity) as expected_qty'), DB::raw('sum(redeemed_quantity) as redeemed_qty'))->groupBy('meeting_event_id'), 'ent', 'ent.meeting_event_id', '=', 'm.id')
            ->select('m.id', 'm.event_name', 'm.start_at', 'h.name as hotel_name', 'h.timezone as hotel_timezone', DB::raw("string_agg(distinct p.name, ', ') as package_names"), DB::raw('coalesce(part.registered, 0) as registered'), DB::raw('coalesce(ent.expected_qty, 0) as expected_qty'), DB::raw('coalesce(ent.redeemed_qty, 0) as redeemed_qty'))
            ->whereBetween('m.start_at', [$from, $to])
            ->when($filter->hotelIds(), fn ($query, $ids) => $query->whereIn('m.hotel_id', $ids))
            ->when($filter->get('package_id'), fn ($query, $id) => $query->where('a.package_id', $id))
            ->when($filter->get('meeting_id'), fn ($query, $id) => $query->where('m.id', $id))
            ->groupBy('m.id', 'h.name', 'h.timezone', 'part.registered', 'ent.expected_qty', 'ent.redeemed_qty')
            ->orderBy('m.start_at');
    }

    private function roomUtilizationReport(ReportFilter $filter): Builder
    {
        [$from, $to] = $this->timezones->rangeBounds($filter->get('date_from'), $filter->get('date_to'), $filter->hotel);
        $days = max(1, $from->diffInDays($to) + 1);
        $availableHours = $days * (int) config('reports.room_utilization_hours_per_day', 24);

        return DB::table('meeting_rooms as r')
            ->leftJoin('hotels as h', 'h.id', '=', 'r.hotel_id')
            ->leftJoin('meeting_events as m', function ($join) use ($from, $to) {
                $join->on('m.meeting_room_id', '=', 'r.id')->whereBetween('m.start_at', [$from, $to]);
            })
            ->select('r.id', 'r.name as room_name', 'h.name as hotel_name', 'h.timezone as hotel_timezone', DB::raw((string) $availableHours.' as available_hours'), DB::raw("coalesce(sum(extract(epoch from (m.end_at - m.start_at)) / 3600) filter (where m.status not in ('CANCELLED', 'NO_SHOW')), 0) as reserved_hours"), DB::raw("coalesce(sum(extract(epoch from (coalesce(m.completed_at, m.end_at) - coalesce(m.started_at, m.start_at))) / 3600) filter (where m.status in ('OCCUPIED', 'COMPLETED')), 0) as occupied_hours"), DB::raw('count(m.id) as total_meetings'), DB::raw("count(m.id) filter (where m.status = 'CANCELLED') as cancelled_meetings"), DB::raw("count(m.id) filter (where m.status = 'NO_SHOW') as no_show_meetings"))
            ->when($filter->hotelIds(), fn ($query, $ids) => $query->whereIn('r.hotel_id', $ids))
            ->when($filter->get('room_id'), fn ($query, $id) => $query->where('r.id', $id))
            ->groupBy('r.id', 'r.name', 'h.name', 'h.timezone')
            ->orderBy('r.name');
    }

    private function formatRow(string $type, array $row, ReportFilter $filter, Collection $hotelTimezones): array
    {
        $hotel = $filter->hotel ?: (object) ['timezone' => $row['hotel_timezone'] ?? $hotelTimezones[$row['hotel_id'] ?? null] ?? config('app.timezone')];
        $includeHotel = $filter->hotel === null;
        $contactAllowed = $filter->user->isSuperAdmin() || $filter->user->can('participant.view');

        $formatted = match ($type) {
            ReportType::MEETINGS->value => [$row['booking_number'] ?? '-', $row['client_name'] ?? '-', $row['event_name'], $row['room_name'] ?? '-', $this->timezones->display($row['start_at'], $hotel, 'Y-m-d'), $this->timezones->display($row['start_at'], $hotel, 'H:i'), $this->timezones->display($row['end_at'], $hotel, 'H:i'), (int) $row['expected_participants'], (int) $row['actual_count'], (int) $row['expected_participants'] > 0 ? round(((int) $row['actual_count'] / (int) $row['expected_participants']) * 100, 2) : 0, $row['package_names'] ?? '-', $row['status']],
            ReportType::PARTICIPANTS->value => array_values(array_filter([$row['full_name'], $row['company_name'] ?? '-', $contactAllowed ? trim(($row['email'] ?? '').' '.($row['phone'] ?? '')) ?: '-' : null, $this->timezones->display($row['registered_at'], $hotel), $this->timezones->display($row['checked_in_at'], $hotel), $row['qr_status'] ?? 'NONE', $row['event_name'], $row['checked_in_at'] ? 'CHECKED_IN' : 'NOT_CHECKED_IN'], fn ($value) => $value !== null)),
            ReportType::REDEMPTIONS->value => [$row['full_name'], $row['event_name'], $row['session_name'], $row['entitlement_type'], $this->timezones->display($row['redeemed_at'] ?: $row['created_at'], $hotel), $row['scanner_name'] ?? '-', $row['device_id'] ?? '-', $row['status'], $row['rejection_code'] ?? '-', $row['override_reason'] ?: ($row['override_id'] ? 'Overridden by #'.$row['override_id'] : '-'), $row['status'] === 'REVERSED' ? 'Reversed' : '-'],
            ReportType::PACKAGE_CONSUMPTION->value => [$row['event_name'], $row['package_names'] ?? '-', (int) $row['expected_qty'], (int) $row['registered'], (int) $row['redeemed_qty'], (int) $row['expected_qty'] - (int) $row['redeemed_qty'], (int) $row['expected_qty'] > 0 ? round(((int) $row['redeemed_qty'] / (int) $row['expected_qty']) * 100, 2) : 0],
            ReportType::ROOM_UTILIZATION->value => [$row['room_name'], ($filter->get('date_from') ?: now()->toDateString()).' to '.($filter->get('date_to') ?: $filter->get('date_from') ?: now()->toDateString()), round((float) $row['reserved_hours'], 2), round((float) $row['occupied_hours'], 2), (float) $row['available_hours'] > 0 ? round(((float) $row['reserved_hours'] / (float) $row['available_hours']) * 100, 2) : 0, (int) $row['total_meetings'] > 0 ? round(((int) $row['cancelled_meetings'] / (int) $row['total_meetings']) * 100, 2) : 0, (int) $row['total_meetings'] > 0 ? round(((int) $row['no_show_meetings'] / (int) $row['total_meetings']) * 100, 2) : 0],
            default => [],
        };

        if ($includeHotel) {
            $formatted[] = $row['hotel_name'] ?? '-';
        }

        return $formatted;
    }
}
