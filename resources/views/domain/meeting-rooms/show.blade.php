<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $room->name,
        'breadcrumbs' => ['Master Data' => null, 'Meeting Rooms' => route('meeting-rooms.index'), $room->name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('meeting-rooms.edit', $room).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <div class="row">
            <div class="col-md-6">
                <p><strong>Hotel:</strong> {{ $room->hotel?->name ?? '-' }}</p>
                <p><strong>Code:</strong> {{ $room->code }}</p>
                <p><strong>Floor:</strong> {{ $room->floor ?: '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Capacity:</strong> {{ $room->capacity }}</p>
                <p><strong>Status:</strong> <span class="badge badge-secondary">{{ $room->operational_status->value ?? $room->operational_status }}</span></p>
                <p><strong>Meetings:</strong> {{ $room->meetings_count }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('meeting-rooms.destroy', $room) }}" class="d-inline" onsubmit="return confirm('Deactivate this meeting room?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger js-disable-on-submit">Deactivate</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
