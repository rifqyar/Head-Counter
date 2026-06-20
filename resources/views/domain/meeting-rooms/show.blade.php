<div class="container-fluid">
    @include('domain._alerts')
    <h4>{{ $room->name }}</h4>
    <p><strong>Code:</strong> {{ $room->code }}</p>
    <p><strong>Floor:</strong> {{ $room->floor ?: '-' }}</p>
    <p><strong>Capacity:</strong> {{ $room->capacity }}</p>
    <p><strong>Status:</strong> {{ $room->operational_status->value ?? $room->operational_status }}</p>
    <a href="{{ route('meeting-rooms.edit', $room) }}" class="btn btn-warning">Edit</a>
    <form method="POST" action="{{ route('meeting-rooms.destroy', $room) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Deactivate</button>
    </form>
</div>
