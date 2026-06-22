@php
    $meetingStatus = $meeting->status->value ?? $meeting->status;
    $checkedInCount = $meeting->attendances->where('attendance_type', \App\Enums\AttendanceType::MEETING_CHECKIN)->unique('participant_id')->count();
@endphp
<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $meeting->event_name,
        'breadcrumbs' => ['Operations' => null, 'Meetings' => route('meetings.index'), $meeting->event_name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('meetings.edit', $meeting).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#meeting-overview" role="tab">Overview</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#meeting-schedule" role="tab">Schedule</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#meeting-participants" role="tab">Participants</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#meeting-attendance" role="tab">Attendance</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#meeting-packages" role="tab">Packages</a></li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="meeting-overview" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span class="badge badge-secondary">{{ $meetingStatus }}</span></p>
                        <p><strong>Booking:</strong> {{ $meeting->booking?->booking_number ?? '-' }}</p>
                        <p><strong>Client:</strong> {{ $meeting->booking?->client?->company_name ?? '-' }}</p>
                        <p><strong>Room:</strong> {{ $meeting->meetingRoom?->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Expected:</strong> {{ $meeting->expected_participants }}</p>
                        <p><strong>Registered:</strong> {{ $meeting->participants->count() }}</p>
                        <p><strong>Checked In:</strong> {{ $checkedInCount }}</p>
                        <p><strong>QR Issued:</strong> {{ $meeting->meeting_qr_issued_at?->format('Y-m-d H:i') ?? '-' }}</p>
                    </div>
                </div>
                @include('domain.meetings._status_actions', ['meeting' => $meeting, 'meetingStatus' => $meetingStatus])
            </div>

            <div class="tab-pane fade" id="meeting-schedule" role="tabpanel">
                <p><strong>Event Date:</strong> {{ $meeting->event_date?->toDateString() }}</p>
                <p><strong>Start:</strong> {{ $meeting->start_at }}</p>
                <p><strong>End:</strong> {{ $meeting->end_at }}</p>
                <p><strong>Check-in Opened:</strong> {{ $meeting->checkin_open_at?->format('Y-m-d H:i') ?? '-' }}</p>
                <p><strong>Started:</strong> {{ $meeting->started_at?->format('Y-m-d H:i') ?? '-' }}</p>
                <p><strong>Completed:</strong> {{ $meeting->completed_at?->format('Y-m-d H:i') ?? '-' }}</p>
            </div>

            <div class="tab-pane fade" id="meeting-participants" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Name</th><th>Company</th><th>Contact</th><th>Status</th><th>Registered</th><th>Checked In</th></tr></thead>
                        <tbody>
                        @forelse ($meeting->participants as $participant)
                            <tr>
                                <td>{{ $participant->full_name }}</td>
                                <td>{{ $participant->company_name ?? '-' }}</td>
                                <td>{{ trim(($participant->email ?? '').' '.($participant->phone ?? '')) ?: '-' }}</td>
                                <td><span class="badge badge-info">{{ $participant->status->value ?? $participant->status }}</span></td>
                                <td>{{ $participant->registered_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $participant->checked_in_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No participants registered yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="meeting-attendance" role="tabpanel">
                <div class="mb-3">
                    <span class="badge badge-primary">Expected {{ $meeting->expected_participants }}</span>
                    <span class="badge badge-info">Registered {{ $meeting->participants->count() }}</span>
                    <span class="badge badge-success">Checked In {{ $checkedInCount }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Participant</th><th>Type</th><th>Attended At</th><th>Verification</th><th>Device</th></tr></thead>
                        <tbody>
                        @forelse ($meeting->attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->participant?->full_name ?? '-' }}</td>
                                <td>{{ $attendance->attendance_type->value ?? $attendance->attendance_type }}</td>
                                <td>{{ $attendance->attended_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $attendance->verification_method }}</td>
                                <td>{{ $attendance->device_id ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No attendance records yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="meeting-packages" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Package</th><th>Quota</th><th>Unit Price</th><th>Entitlements</th></tr></thead>
                        <tbody>
                        @forelse ($meeting->packageAssignments as $assignment)
                            <tr>
                                <td>{{ $assignment->package?->name ?? '-' }}</td>
                                <td>{{ $assignment->participant_quota }}</td>
                                <td>{{ number_format((float) $assignment->unit_price, 0) }}</td>
                                <td>
                                    @forelse ($assignment->package?->entitlements ?? [] as $entitlement)
                                        <span class="badge badge-info">{{ $entitlement->entitlement_type->value ?? $entitlement->entitlement_type }} x {{ $entitlement->quantity }}</span>
                                    @empty
                                        <span class="text-muted">No entitlements</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No package assigned.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
