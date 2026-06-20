<div class="container-fluid">
    @include('domain._alerts')
    <h4>{{ $participant->full_name }}</h4>
    <p><strong>Number:</strong> {{ $participant->participant_number }}</p>
    <p><strong>Meeting:</strong> {{ $participant->meetingEvent?->event_name ?? '-' }}</p>
    <p><strong>Contact:</strong> {{ $participant->email ?: '-' }} / {{ $participant->phone ?: '-' }}</p>
    <p><strong>Status:</strong> {{ $participant->status->value ?? $participant->status }}</p>
    <a href="{{ route('participants.edit', $participant) }}" class="btn btn-warning">Edit</a>
    @can('participant.qr.manage')
        <a href="{{ route('participants.qr.show', $participant) }}" class="btn btn-info">Manage QR</a>
    @endcan
    <form method="POST" action="{{ route('participants.destroy', $participant) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Cancel</button>
    </form>
</div>
