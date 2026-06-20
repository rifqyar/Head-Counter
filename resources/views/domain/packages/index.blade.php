<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Packages</h4>
        <a href="{{ route('packages.create') }}" class="btn btn-primary">Create Package</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>Code</th><th>Name</th><th>Price</th><th>Active</th><th>Entitlements</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($packages as $package)
            <tr><td>{{ $package->code }}</td><td>{{ $package->name }}</td><td>{{ $package->price }}</td><td>{{ $package->is_active ? 'YES' : 'NO' }}</td><td>{{ $package->entitlements->map(fn ($e) => ($e->entitlement_type->value ?? $e->entitlement_type).': '.$e->quantity)->implode(', ') }}</td><td><a href="{{ route('packages.show', $package) }}" class="btn btn-sm btn-info">Detail</a> <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-warning">Edit</a></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No packages found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
