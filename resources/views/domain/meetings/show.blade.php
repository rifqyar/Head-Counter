<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $meeting->event_name,
        'breadcrumbs' => ['Operations' => null, 'Meetings' => route('meetings.index'), $meeting->event_name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('meetings.edit', $meeting).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="#overview">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="#schedule">Schedule</a></li>
            <li class="nav-item"><a class="nav-link" href="#participants">Participants</a></li>
            <li class="nav-item"><a class="nav-link" href="#attendance">Attendance</a></li>
            <li class="nav-item"><a class="nav-link" href="#packages">Packages</a></li>
        </ul>
        <p class="mt-3"><strong>Status:</strong> <span class="badge badge-secondary">{{ $meeting->status->value ?? $meeting->status }}</span></p>
        <p><strong>Schedule:</strong> {{ $meeting->start_at }} - {{ $meeting->end_at }}</p>
        <p><strong>Room:</strong> {{ $meeting->meetingRoom?->name ?? '-' }}</p>
        @if (! in_array($meeting->status->value ?? $meeting->status, ['COMPLETED', 'CANCELLED', 'NO_SHOW'], true))
            <form method="POST" action="{{ route('meetings.transition', $meeting) }}" class="d-inline">
                @csrf
                <select name="status" class="form-control d-inline-block w-auto">
                    @foreach (['CHECKIN_OPEN', 'OCCUPIED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary js-disable-on-submit">Transition</button>
            </form>
            <form method="POST" action="{{ route('meetings.destroy', $meeting) }}" class="d-inline" onsubmit="return confirm('Cancel this meeting?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger js-disable-on-submit">Cancel</button>
            </form>
        @endif
    @endcomponent
</div>
@include('domain._datatable')
