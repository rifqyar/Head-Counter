<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Clients</h4>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">Create Client</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>External ID</th><th>Company</th><th>Contact</th><th>Email</th><th>Phone</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($clients as $client)
            <tr><td>{{ $client->external_id }}</td><td>{{ $client->company_name }}</td><td>{{ $client->contact_name }}</td><td>{{ $client->contact_email }}</td><td>{{ $client->contact_phone }}</td><td><a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info">Detail</a> <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning">Edit</a></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No clients found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
