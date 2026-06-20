<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Packages',
        'breadcrumbs' => ['Master Data' => null, 'Packages' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('packages.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Package</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Code</th><th>Name</th><th>Price</th><th>Active</th><th>Entitlements</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($packages as $package)
                    <tr><td>{{ $package->code }}</td><td>{{ $package->name }}</td><td>{{ $package->price }}</td><td><span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }}">{{ $package->is_active ? 'YES' : 'NO' }}</span></td><td>{{ $package->entitlements->map(fn ($e) => ($e->entitlement_type->value ?? $e->entitlement_type).': '.$e->quantity)->implode(', ') }}</td><td><a href="{{ route('packages.show', $package) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-warning spa_route">Edit</a></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No packages found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
