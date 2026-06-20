<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Participants</h4>
        <a href="{{ route('participants.create') }}" class="btn btn-primary">Register Participant</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>Number</th><th>Name</th><th>Meeting</th><th>Email</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($participants as $participant)
            <tr><td>{{ $participant->participant_number }}</td><td>{{ $participant->full_name }}</td><td>{{ $participant->meetingEvent?->event_name ?? '-' }}</td><td>{{ $participant->email }}</td><td>{{ $participant->phone }}</td><td>{{ $participant->status->value ?? $participant->status }}</td><td><a href="{{ route('participants.show', $participant) }}" class="btn btn-sm btn-info">Detail</a> <a href="{{ route('participants.edit', $participant) }}" class="btn btn-sm btn-warning">Edit</a> @can('participant.qr.manage')<a href="{{ route('participants.qr.show', $participant) }}" class="btn btn-sm btn-outline-info">QR</a>@endcan</td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No participants found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
