<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Clients',
        'breadcrumbs' => ['Master Data' => null, 'Clients' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('clients.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Client</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>External ID</th><th>Company</th><th>Hotels</th><th>Contact</th><th>Email</th><th>Phone</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $client->external_id ?: '-' }}</td>
                        <td>{{ $client->company_name }}</td>
                        <td>
                            @forelse ($client->hotels as $hotel)
                                <span class="badge badge-info">{{ $hotel->code }}</span>
                            @empty
                                <span class="text-muted">Unassociated</span>
                            @endforelse
                        </td>
                        <td>{{ $client->contact_name ?: '-' }}</td>
                        <td>{{ $client->contact_email ?: '-' }}</td>
                        <td>{{ $client->contact_phone ?: '-' }}</td>
                        <td><a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning spa_route">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No clients are associated with the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
