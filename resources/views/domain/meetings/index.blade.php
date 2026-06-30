<div class="container-fluid">
    @php($isAttendanceContext = ($context ?? 'master') === 'attendance')
    @php($usesOperationalMeetingContext = $isAttendanceContext || ! auth()->user()?->can('meeting.create'))
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $usesOperationalMeetingContext ? 'Meeting Attendance Operations' : 'Meetings',
        'breadcrumbs' => $usesOperationalMeetingContext ? ['Transaction' => null, 'Meeting Attendance' => null] : ['Master Data' => null, 'Meetings' => null],
        'actions' => $usesOperationalMeetingContext ? null : new \Illuminate\Support\HtmlString('<a href="'.route('meetings.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Meeting</a>'),
    ])
    @component('domain._card')
        <form method="GET" action="{{ $usesOperationalMeetingContext ? route('meeting-attendance.index') : route('meetings.index') }}" class="mb-3">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Meeting Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
                </div>
                <div class="form-group col-md-4">
                    <label>Client</label>
                    <input type="text" name="client" class="form-control" value="{{ $filters['client'] ?? '' }}" placeholder="Client name, code, or contact">
                </div>
                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All statuses</option>
                        @foreach (['DRAFT', 'SCHEDULED', 'CHECKIN_OPEN', 'OCCUPIED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ $usesOperationalMeetingContext ? route('meeting-attendance.index') : route('meetings.index') }}" class="btn btn-outline-secondary spa_route">Reset</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Name</th><th>Client</th><th>Room</th><th>Start</th><th>End</th><th>Expected</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($meetings as $meeting)
                    @php($meetingStatus = $meeting->status->value ?? $meeting->status)
                    @php($nextStatuses = \App\Services\MeetingStateTransition::ALLOWED[$meetingStatus] ?? [])
                    <tr>
                        <td>{{ $meeting->event_name }}</td>
                        <td>{{ $meeting->booking?->client?->company_name ?? '-' }}</td>
                        <td>{{ $meeting->meetingRoom?->name ?? '-' }}</td>
                        <td>{{ $meeting->start_at }}</td>
                        <td>{{ $meeting->end_at }}</td>
                        <td>{{ $meeting->expected_participants }}</td>
                        <td><span class="badge badge-secondary">{{ $meetingStatus }}</span></td>
                        <td>
                            <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-sm btn-info spa_route">Detail</a>
                            <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-sm btn-warning spa_route">Edit</a>
                            @if ($nextStatuses !== [])
                                <form method="POST" action="{{ route('meetings.transition', $meeting) }}" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-control form-control-sm select2 d-inline-block w-auto">
                                        @foreach ($nextStatuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary js-disable-on-submit">Change</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No meetings found for the selected filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $meetings->links() }}
    @endcomponent
</div>
@include('domain._datatable')
