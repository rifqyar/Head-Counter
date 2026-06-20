<div class="container-fluid">
    @include('domain._alerts')
    <h4>{{ $package->name }}</h4>
    <p><strong>Code:</strong> {{ $package->code }}</p>
    <p><strong>Price:</strong> {{ $package->price }}</p>
    <p><strong>Active:</strong> {{ $package->is_active ? 'Yes' : 'No' }}</p>
    <p><strong>Description:</strong> {{ $package->description ?: '-' }}</p>
    <a href="{{ route('packages.edit', $package) }}" class="btn btn-warning">Edit</a>
    <form method="POST" action="{{ route('packages.destroy', $package) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Deactivate</button>
    </form>
</div>
