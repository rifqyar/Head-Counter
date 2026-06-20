<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Participants',
        'breadcrumbs' => ['Operations' => null, 'Participants' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('participants.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Register Participant</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Number</th><th>Name</th><th>Meeting</th><th>Email</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($participants as $participant)
                    <tr><td>{{ $participant->participant_number }}</td><td>{{ $participant->full_name }}</td><td>{{ $participant->meetingEvent?->event_name ?? '-' }}</td><td>{{ $participant->email }}</td><td>{{ $participant->phone }}</td><td><span class="badge badge-secondary">{{ $participant->status->value ?? $participant->status }}</span></td><td><a href="{{ route('participants.show', $participant) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('participants.edit', $participant) }}" class="btn btn-sm btn-warning spa_route">Edit</a> @can('participant.qr.manage')<a href="{{ route('participants.qr.show', $participant) }}" class="btn btn-sm btn-outline-info spa_route">QR</a>@endcan</td></tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No participants found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
