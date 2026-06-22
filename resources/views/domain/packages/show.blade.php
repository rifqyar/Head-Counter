<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $package->name,
        'breadcrumbs' => ['Master Data' => null, 'Packages' => route('packages.index'), $package->name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('packages.edit', $package).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <p><strong>Code:</strong> {{ $package->code }}</p>
        <p><strong>Price:</strong> {{ $package->price }}</p>
        <p><strong>Active:</strong> <span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }}">{{ $package->is_active ? 'Yes' : 'No' }}</span></p>
        <p><strong>Description:</strong> {{ $package->description ?: '-' }}</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead><tr><th>Entitlement</th><th>Quantity</th><th>Notes</th></tr></thead>
                <tbody>
                @forelse ($package->entitlements as $entitlement)
                    <tr><td>{{ $entitlement->entitlement_type->value ?? $entitlement->entitlement_type }}</td><td>{{ $entitlement->quantity }}</td><td>{{ $entitlement->metadata['notes'] ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">No entitlements configured.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('packages.destroy', $package) }}" class="d-inline" onsubmit="return confirm('Deactivate this package?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger js-disable-on-submit">Deactivate</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
