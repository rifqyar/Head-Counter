<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $participant->full_name,
        'breadcrumbs' => ['Operations' => null, 'Participants' => route('participants.index'), $participant->full_name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('participants.edit', $participant).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <p><strong>Number:</strong> {{ $participant->participant_number }}</p>
        <p><strong>Meeting:</strong> {{ $participant->meetingEvent?->event_name ?? '-' }}</p>
        <p><strong>Contact:</strong> {{ $participant->email ?: '-' }} / {{ $participant->phone ?: '-' }}</p>
        <p><strong>Status:</strong> <span class="badge badge-secondary">{{ $participant->status->value ?? $participant->status }}</span></p>
        @can('participant.qr.manage')
            <a href="{{ route('participants.qr.show', $participant) }}" class="btn btn-info spa_route">Manage QR</a>
        @endcan
        <form method="POST" action="{{ route('participants.destroy', $participant) }}" class="d-inline" onsubmit="return confirm('Cancel this participant?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger js-disable-on-submit">Cancel</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
