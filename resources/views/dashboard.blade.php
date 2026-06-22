@php
    $attendance = (float) $metrics['attendance_percentage'];
    $redemption = $metrics['redemption_summary'];
    $coffeePercent = $redemption['coffee_break_total'] > 0 ? round(($redemption['coffee_break_redeemed'] / $redemption['coffee_break_total']) * 100) : 0;
    $lunchPercent = $redemption['lunch_total'] > 0 ? round(($redemption['lunch_redeemed'] / $redemption['lunch_total']) * 100) : 0;
    $alertCount = collect($alerts)->sum(fn ($items) => count($items));
    $roomBadge = [
        'AVAILABLE' => 'success',
        'RESERVED' => 'info',
        'OCCUPIED' => 'warning',
        'CLEANING' => 'primary',
        'MAINTENANCE' => 'danger',
        'INACTIVE' => 'secondary',
    ];
@endphp

<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #0f766e 0%, #155e75 100%);
        border-radius: 8px;
        color: #fff;
        padding: 22px 24px;
        margin-bottom: 20px;
        box-shadow: 0 10px 24px rgba(21, 94, 117, .16);
    }

    .dashboard-hero h3,
    .dashboard-hero p {
        color: #fff;
    }

    .ops-filter {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .ops-card {
        border: 1px solid #eef1f4;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(33, 37, 41, .04);
        min-height: 118px;
    }

    .ops-card .card-body {
        padding: 18px;
    }

    .ops-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ecfeff;
        color: #0e7490;
        font-size: 22px;
    }

    .ops-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .ops-value {
        font-size: 26px;
        font-weight: 700;
        color: #263238;
        line-height: 1.15;
    }

    .timeline-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .timeline-list li {
        display: flex;
        border-bottom: 1px solid #eef1f4;
        padding: 12px 0;
    }

    .timeline-list li:last-child {
        border-bottom: 0;
    }

    .timeline-time {
        width: 82px;
        flex: 0 0 82px;
        color: #0e7490;
        font-weight: 700;
    }

    .alert-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .alert-list li {
        padding: 9px 0;
        border-bottom: 1px solid #eef1f4;
    }

    .alert-list li:last-child {
        border-bottom: 0;
    }

    .room-pill {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #eef1f4;
        border-radius: 8px;
        padding: 11px 12px;
        margin-bottom: 10px;
        background: #fff;
    }

    .progress {
        height: 8px;
        border-radius: 8px;
    }
</style>

<div class="container-fluid">
    @include('domain._alerts')

    <div class="dashboard-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="text-uppercase small mb-2">Operational Dashboard</div>
                <h3 class="mb-2">{{ $metrics['hotel']?->name ?? 'All Authorized Hotels' }}</h3>
                <p class="mb-0">{{ $metrics['date'] }} | {{ $metrics['timezone'] }} | {{ $metrics['meetings_today_count'] }} meeting(s) today</p>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                <div class="h2 mb-0">{{ $attendance }}%</div>
                <div class="small">Attendance rate</div>
                <div class="progress mt-2 bg-white">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, $attendance) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('dashboard.index') }}" class="ops-filter">
        <div class="form-row align-items-end">
            @if ($filters['hotels']->isNotEmpty())
                <div class="form-group col-lg-3 col-md-6">
                    <label>Hotel</label>
                    <select name="hotel_id" class="form-control select2">
                        <option value="">All authorized hotels</option>
                        @foreach ($filters['hotels'] as $hotel)
                            <option value="{{ $hotel->id }}" @selected(($activeFilters['hotel_id'] ?? null) == $hotel->id)>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="form-group col-lg-2 col-md-6">
                <label>Date</label>
                <input type="date" name="date" value="{{ $activeFilters['date'] ?? now()->toDateString() }}" class="form-control">
            </div>
            <div class="form-group col-lg-2 col-md-6">
                <label>Room</label>
                <select name="room_id" class="form-control select2">
                    <option value="">All rooms</option>
                    @foreach ($filters['rooms'] as $room)
                        <option value="{{ $room->id }}" @selected(($activeFilters['room_id'] ?? null) == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-lg-2 col-md-6">
                <label>Client</label>
                <select name="client_id" class="form-control select2">
                    <option value="">All clients</option>
                    @foreach ($filters['clients'] as $client)
                        <option value="{{ $client->id }}" @selected(($activeFilters['client_id'] ?? null) == $client->id)>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-lg-2 col-md-6">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All statuses</option>
                    @foreach ($filters['statuses'] as $status)
                        <option value="{{ $status->value }}" @selected(($activeFilters['status'] ?? null) === $status->value)>{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-lg-1 col-md-6">
                <button class="btn btn-primary btn-block">Apply</button>
            </div>
        </div>
    </form>

    <div class="row">
        @foreach ([
            ['Meetings Today', $metrics['meetings_today_count'], 'mdi-calendar-check'],
            ['Expected Guests', $metrics['expected_participants'], 'mdi-account-multiple'],
            ['Registered', $metrics['registered_participants'], 'mdi-account-plus'],
            ['Checked In', $metrics['checked_in_participants'], 'mdi-account-check'],
            ['Successful Scans', $redemption['successful_redemptions'], 'mdi-qrcode-scan'],
            ['Open Alerts', $alertCount, 'mdi-alert-circle'],
        ] as [$label, $value, $icon])
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card ops-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="ops-label">{{ $label }}</div>
                                <div class="ops-value">{{ $value }}</div>
                            </div>
                            <span class="ops-icon"><i class="mdi {{ $icon }}"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-4">
            @component('domain._card')
                <h4 class="card-title">Room Status</h4>
                @foreach ($metrics['room_counts'] as $status => $count)
                    <div class="room-pill">
                        <span><span class="badge badge-{{ $roomBadge[$status] ?? 'secondary' }}">{{ $status }}</span></span>
                        <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            @endcomponent
        </div>
        <div class="col-lg-4">
            @component('domain._card')
                <h4 class="card-title">Attendance</h4>
                <div class="mb-4">
                    <div class="d-flex justify-content-between"><span>Checked in</span><strong>{{ $metrics['checked_in_participants'] }} / {{ $metrics['expected_participants'] }}</strong></div>
                    <div class="progress mt-2"><div class="progress-bar bg-success" style="width: {{ min(100, $attendance) }}%"></div></div>
                </div>
                <div class="row text-center">
                    <div class="col-6 border-right">
                        <div class="ops-value">{{ $metrics['registered_participants'] }}</div>
                        <div class="ops-label">Registered</div>
                    </div>
                    <div class="col-6">
                        <div class="ops-value">{{ $metrics['checked_in_participants'] }}</div>
                        <div class="ops-label">Checked In</div>
                    </div>
                </div>
            @endcomponent
        </div>
        <div class="col-lg-4">
            @component('domain._card')
                <h4 class="card-title">Redemption Summary</h4>
                <div class="mb-3">
                    <div class="d-flex justify-content-between"><span>Coffee Break</span><strong>{{ $redemption['coffee_break_redeemed'] }} / {{ $redemption['coffee_break_total'] }}</strong></div>
                    <div class="progress mt-2"><div class="progress-bar bg-info" style="width: {{ min(100, $coffeePercent) }}%"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between"><span>Lunch</span><strong>{{ $redemption['lunch_redeemed'] }} / {{ $redemption['lunch_total'] }}</strong></div>
                    <div class="progress mt-2"><div class="progress-bar bg-warning" style="width: {{ min(100, $lunchPercent) }}%"></div></div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Success {{ $redemption['successful_redemptions'] }}</span>
                    <span>Rejected {{ $redemption['rejected_scans'] }}</span>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            @component('domain._card')
                <h4 class="card-title">Today's Meetings</h4>
                <ul class="timeline-list">
                    @forelse ($metrics['meetings_today'] as $meeting)
                        <li>
                            <div class="timeline-time">{{ $meeting->start_at->timezone($meeting->hotel?->timezone ?? config('app.timezone'))->format('H:i') }}</div>
                            <div>
                                <strong>{{ $meeting->event_name }}</strong>
                                <div class="text-muted small">{{ $meeting->meetingRoom?->name ?? 'No room' }} | {{ $meeting->booking?->client?->company_name ?? 'No client' }}</div>
                                <span class="badge badge-info mt-1">{{ $meeting->status->value ?? $meeting->status }}</span>
                            </div>
                        </li>
                    @empty
                        <li><div class="text-muted">No meetings for the selected date.</div></li>
                    @endforelse
                </ul>
            @endcomponent
        </div>
        <div class="col-lg-6">
            @component('domain._card')
                <h4 class="card-title">Upcoming Meetings</h4>
                <ul class="timeline-list">
                    @forelse ($metrics['upcoming_meetings'] as $meeting)
                        <li>
                            <div class="timeline-time">{{ $meeting->start_at->timezone($meeting->hotel?->timezone ?? config('app.timezone'))->format('M d') }}</div>
                            <div>
                                <strong>{{ $meeting->event_name }}</strong>
                                <div class="text-muted small">{{ $meeting->start_at->timezone($meeting->hotel?->timezone ?? config('app.timezone'))->format('H:i') }} | {{ $meeting->meetingRoom?->name ?? 'No room' }} | {{ $meeting->hotel?->code }}</div>
                            </div>
                        </li>
                    @empty
                        <li><div class="text-muted">No upcoming meetings in the configured window.</div></li>
                    @endforelse
                </ul>
            @endcomponent
        </div>
    </div>

    @component('domain._card')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Operational Alerts</h4>
            <span class="badge badge-{{ $alertCount > 0 ? 'warning' : 'success' }}">{{ $alertCount }} open</span>
        </div>
        <div class="row">
            @foreach ([
                'Starting Soon' => $alerts['startingSoon'],
                'Running Late' => $alerts['runningLate'],
                'Over Capacity' => $alerts['overCapacity'],
                'Open Meal Sessions' => $alerts['openMealSessions'],
                'Scanner Failures' => $alerts['recentFailures'],
            ] as $title => $items)
                <div class="col-lg-4 col-md-6 mb-3">
                    <h6>{{ $title }}</h6>
                    <ul class="alert-list">
                        @forelse ($items as $item)
                            <li>{{ $item->event_name ?? $item->name ?? $item->full_name ?? $item->redemption_number ?? 'Alert' }}</li>
                        @empty
                            <li class="text-muted">Clear</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
            <div class="col-lg-4 col-md-6 mb-3">
                <h6>Room Conflicts</h6>
                <ul class="alert-list">
                    @forelse ($alerts['roomConflicts'] as $conflict)
                        <li>{{ $conflict->first_event }} / {{ $conflict->second_event }}</li>
                    @empty
                        <li class="text-muted">Clear</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endcomponent
</div>
