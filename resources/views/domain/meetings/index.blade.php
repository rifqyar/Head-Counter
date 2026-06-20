<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Meetings</h4>
        <a href="{{ route('meetings.create') }}" class="btn btn-primary">Create Meeting</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>Name</th><th>Room</th><th>Start</th><th>End</th><th>Expected</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($meetings as $meeting)
            <tr><td>{{ $meeting->event_name }}</td><td>{{ $meeting->meetingRoom?->name ?? '-' }}</td><td>{{ $meeting->start_at }}</td><td>{{ $meeting->end_at }}</td><td>{{ $meeting->expected_participants }}</td><td>{{ $meeting->status->value ?? $meeting->status }}</td><td><a href="{{ route('meetings.show', $meeting) }}" class="btn btn-sm btn-info">Detail</a> <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-sm btn-warning">Edit</a></td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No meetings found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
