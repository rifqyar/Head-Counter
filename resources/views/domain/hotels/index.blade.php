<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Hotels',
        'breadcrumbs' => ['Master Data' => null, 'Hotels' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('hotels.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Hotel</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Code</th><th>Name</th><th>Timezone</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($hotels as $hotel)
                        <tr>
                            <td>{{ $hotel->code }}</td>
                            <td>{{ $hotel->name }}</td>
                            <td>{{ $hotel->timezone }}</td>
                            <td><span class="badge badge-{{ ($hotel->status->value ?? $hotel->status) === 'ACTIVE' ? 'success' : 'secondary' }}">{{ $hotel->status->value ?? $hotel->status }}</span></td>
                            <td>
                                <a href="{{ route('hotels.show', $hotel) }}" class="btn btn-sm btn-info spa_route">Detail</a>
                                <a href="{{ route('hotels.edit', $hotel) }}" class="btn btn-sm btn-warning spa_route">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No hotels found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
