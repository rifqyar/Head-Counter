<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Meeting Rooms</h4>
        <a href="{{ route('meeting-rooms.create') }}" class="btn btn-primary">Create Room</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>Code</th><th>Name</th><th>Floor</th><th>Capacity</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($rooms as $room)
            <tr>
                <td>{{ $room->code }}</td><td>{{ $room->name }}</td><td>{{ $room->floor ?: '-' }}</td><td>{{ $room->capacity }}</td>
                <td>{{ $room->operational_status->value ?? $room->operational_status }}</td>
                <td><a href="{{ route('meeting-rooms.show', $room) }}" class="btn btn-sm btn-info">Detail</a> <a href="{{ route('meeting-rooms.edit', $room) }}" class="btn btn-sm btn-warning">Edit</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No rooms found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
