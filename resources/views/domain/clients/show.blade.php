<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $client->company_name,
        'breadcrumbs' => ['Master Data' => null, 'Clients' => route('clients.index'), $client->company_name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('clients.edit', $client).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <div class="row">
            <div class="col-md-6">
                <p><strong>External ID:</strong> {{ $client->external_id ?: '-' }}</p>
                <p><strong>Contact:</strong> {{ $client->contact_name ?: '-' }} / {{ $client->contact_email ?: '-' }}</p>
                <p><strong>Phone:</strong> {{ $client->contact_phone ?: '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Tax Number:</strong> {{ $client->tax_number ?: '-' }}</p>
                <p><strong>Billing Address:</strong> {{ $client->billing_address ?: '-' }}</p>
                <p><strong>Associated Hotels:</strong>
                    @forelse ($client->hotels as $hotel)
                        <span class="badge badge-info">{{ $hotel->code }} - {{ $hotel->name }}</span>
                    @empty
                        <span class="text-muted">None</span>
                    @endforelse
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline" onsubmit="return confirm('Delete this client? Existing bookings may prevent this action.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger js-disable-on-submit">Delete</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
