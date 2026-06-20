<div class="container-fluid">
    @include('domain._alerts')
    <h4>{{ $hotel->name }}</h4>
    <p><strong>Code:</strong> {{ $hotel->code }}</p>
    <p><strong>Address:</strong> {{ $hotel->address ?: '-' }}</p>
    <p><strong>Status:</strong> {{ $hotel->status->value ?? $hotel->status }}</p>
    <a href="{{ route('hotels.edit', $hotel) }}" class="btn btn-warning">Edit</a>
    <form method="POST" action="{{ route('hotels.destroy', $hotel) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Deactivate</button>
    </form>
</div>
