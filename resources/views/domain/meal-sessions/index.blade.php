<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Meal Sessions',
        'breadcrumbs' => ['Operations' => null, 'Meal Sessions' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('meal-sessions.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Meal Session</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Meeting</th><th>Name</th><th>Type</th><th>Window</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($sessions as $session)
                    <tr>
                        <td>{{ $session->meetingEvent?->event_name }}</td>
                        <td>{{ $session->name }}</td>
                        <td>{{ $session->entitlement_type->value ?? $session->entitlement_type }}</td>
                        <td>{{ $session->starts_at?->format('d M H:i') }} - {{ $session->ends_at?->format('H:i') }}</td>
                        <td><span class="badge badge-secondary">{{ $session->status->value ?? $session->status }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('meal-sessions.edit', $session) }}" class="btn btn-sm btn-warning spa_route">Edit</a>
                            <form method="POST" action="{{ route('meal-sessions.open', $session) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success js-disable-on-submit">Open</button></form>
                            <form method="POST" action="{{ route('meal-sessions.close', $session) }}" class="d-inline">@csrf<button class="btn btn-sm btn-secondary js-disable-on-submit">Close</button></form>
                            <form method="POST" action="{{ route('meal-sessions.cancel', $session) }}" class="d-inline" onsubmit="return confirm('Cancel this meal session?')">@csrf<button class="btn btn-sm btn-danger js-disable-on-submit">Cancel</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No meal sessions found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $sessions->links() }}
    @endcomponent
</div>
@include('domain._datatable')
