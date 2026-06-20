<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Meetings',
        'breadcrumbs' => ['Operations' => null, 'Meetings' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('meetings.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Meeting</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Name</th><th>Room</th><th>Start</th><th>End</th><th>Expected</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($meetings as $meeting)
                    <tr><td>{{ $meeting->event_name }}</td><td>{{ $meeting->meetingRoom?->name ?? '-' }}</td><td>{{ $meeting->start_at }}</td><td>{{ $meeting->end_at }}</td><td>{{ $meeting->expected_participants }}</td><td><span class="badge badge-secondary">{{ $meeting->status->value ?? $meeting->status }}</span></td><td><a href="{{ route('meetings.show', $meeting) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-sm btn-warning spa_route">Edit</a></td></tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No meetings found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
