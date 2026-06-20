<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Meeting Rooms',
        'breadcrumbs' => ['Master Data' => null, 'Meeting Rooms' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('meeting-rooms.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Room</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr>@if (auth()->user()?->isSuperAdmin())<th>Hotel</th>@endif<th>Code</th><th>Name</th><th>Floor</th><th>Capacity</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        @if (auth()->user()?->isSuperAdmin())<td>{{ $room->hotel?->code ?? '-' }}</td>@endif
                        <td>{{ $room->code }}</td><td>{{ $room->name }}</td><td>{{ $room->floor ?: '-' }}</td><td>{{ $room->capacity }}</td>
                        <td><span class="badge badge-{{ ($room->operational_status->value ?? $room->operational_status) === 'AVAILABLE' ? 'success' : 'secondary' }}">{{ $room->operational_status->value ?? $room->operational_status }}</span></td>
                        <td><a href="{{ route('meeting-rooms.show', $room) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('meeting-rooms.edit', $room) }}" class="btn btn-sm btn-warning spa_route">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()?->isSuperAdmin() ? 7 : 6 }}" class="text-center text-muted">No rooms found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
