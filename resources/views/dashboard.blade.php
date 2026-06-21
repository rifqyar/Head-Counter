<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Operational Dashboard',
        'breadcrumbs' => ['Dashboard' => null],
    ])

    @component('domain._card')
        <form method="GET" action="{{ route('dashboard.index') }}" class="row">
            @if ($filters['hotels']->isNotEmpty())
                <div class="col-md-3 form-group">
                    <label>Hotel</label>
                    <select name="hotel_id" class="form-control">
                        <option value="">All authorized hotels</option>
                        @foreach ($filters['hotels'] as $hotel)
                            <option value="{{ $hotel->id }}" @selected(($activeFilters['hotel_id'] ?? null) == $hotel->id)>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-2 form-group">
                <label>Date</label>
                <input type="date" name="date" value="{{ $activeFilters['date'] ?? now()->toDateString() }}" class="form-control">
            </div>
            <div class="col-md-2 form-group">
                <label>Room</label>
                <select name="room_id" class="form-control">
                    <option value="">All rooms</option>
                    @foreach ($filters['rooms'] as $room)
                        <option value="{{ $room->id }}" @selected(($activeFilters['room_id'] ?? null) == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label>Client</label>
                <select name="client_id" class="form-control">
                    <option value="">All clients</option>
                    @foreach ($filters['clients'] as $client)
                        <option value="{{ $client->id }}" @selected(($activeFilters['client_id'] ?? null) == $client->id)>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All statuses</option>
                    @foreach ($filters['statuses'] as $status)
                        <option value="{{ $status->value }}" @selected(($activeFilters['status'] ?? null) === $status->value)>{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 form-group d-flex align-items-end">
                <button class="btn btn-primary btn-block">Filter</button>
            </div>
        </form>
        <div class="text-muted small">Context: {{ $metrics['hotel']?->name ?? 'All authorized hotels' }} | Timezone: {{ $metrics['timezone'] }}</div>
    @endcomponent

    <div class="row">
        @foreach ([
            ['Meetings Today', $metrics['meetings_today_count'], 'mdi-calendar-check'],
            ['Expected Participants', $metrics['expected_participants'], 'mdi-account-multiple'],
            ['Registered', $metrics['registered_participants'], 'mdi-account-plus'],
            ['Checked In', $metrics['checked_in_participants'], 'mdi-account-check'],
            ['Attendance %', $metrics['attendance_percentage'].'%', 'mdi-percent'],
            ['Rejected Scans', $metrics['redemption_summary']['rejected_scans'], 'mdi-alert-circle'],
        ] as [$label, $value, $icon])
            <div class="col-lg-2 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="mdi {{ $icon }} font-24 text-info mr-2"></i>
                            <div>
                                <h4 class="mb-0">{{ $value }}</h4>
                                <small class="text-muted">{{ $label }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-6">
            @component('domain._card')
                <h4 class="card-title">Room Status</h4>
                <div class="row">
                    @foreach ($metrics['room_counts'] as $status => $count)
                        <div class="col-6 col-md-4 mb-2"><span class="badge badge-secondary">{{ $status }}</span> {{ $count }}</div>
                    @endforeach
                </div>
            @endcomponent
        </div>
        <div class="col-lg-6">
            @component('domain._card')
                <h4 class="card-title">Redemption Summary</h4>
                <div class="row">
                    <div class="col-md-6">Coffee break: {{ $metrics['redemption_summary']['coffee_break_redeemed'] }} / {{ $metrics['redemption_summary']['coffee_break_total'] }}</div>
                    <div class="col-md-6">Lunch: {{ $metrics['redemption_summary']['lunch_redeemed'] }} / {{ $metrics['redemption_summary']['lunch_total'] }}</div>
                    <div class="col-md-6">Successful: {{ $metrics['redemption_summary']['successful_redemptions'] }}</div>
                    <div class="col-md-6">Rejected: {{ $metrics['redemption_summary']['rejected_scans'] }}</div>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            @component('domain._card')
                <h4 class="card-title">Today's Meetings</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Meeting</th><th>Room</th><th>Start</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($metrics['meetings_today'] as $meeting)
                                <tr><td>{{ $meeting->event_name }}</td><td>{{ $meeting->meetingRoom?->name ?? '-' }}</td><td>{{ $meeting->start_at->timezone($meeting->hotel?->timezone ?? config('app.timezone'))->format('H:i') }}</td><td><span class="badge badge-info">{{ $meeting->status->value ?? $meeting->status }}</span></td></tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No meetings for the selected date.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="col-lg-6">
            @component('domain._card')
                <h4 class="card-title">Upcoming Meetings</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Meeting</th><th>Room</th><th>Start</th><th>Hotel</th></tr></thead>
                        <tbody>
                            @forelse ($metrics['upcoming_meetings'] as $meeting)
                                <tr><td>{{ $meeting->event_name }}</td><td>{{ $meeting->meetingRoom?->name ?? '-' }}</td><td>{{ $meeting->start_at->timezone($meeting->hotel?->timezone ?? config('app.timezone'))->format('Y-m-d H:i') }}</td><td>{{ $meeting->hotel?->code }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No upcoming meetings in the configured window.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

    @component('domain._card')
        <h4 class="card-title">Operational Alerts</h4>
        <div class="row">
            @foreach ([
                'Starting Soon' => $alerts['startingSoon'],
                'Running Beyond Schedule' => $alerts['runningLate'],
                'Over Capacity' => $alerts['overCapacity'],
                'Open Meal Sessions' => $alerts['openMealSessions'],
                'Recent Scanner Failures' => $alerts['recentFailures'],
            ] as $title => $items)
                <div class="col-md-4 mb-3">
                    <h6>{{ $title }}</h6>
                    <ul class="list-unstyled mb-0">
                        @forelse ($items as $item)
                            <li class="border-bottom py-1">{{ $item->event_name ?? $item->name ?? $item->full_name ?? $item->redemption_number ?? 'Alert' }}</li>
                        @empty
                            <li class="text-muted">No alerts.</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
            <div class="col-md-4 mb-3">
                <h6>Room Conflicts</h6>
                <ul class="list-unstyled mb-0">
                    @forelse ($alerts['roomConflicts'] as $conflict)
                        <li class="border-bottom py-1">{{ $conflict->first_event }} / {{ $conflict->second_event }}</li>
                    @empty
                        <li class="text-muted">No conflicts.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endcomponent
</div>
