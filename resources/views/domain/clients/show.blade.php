<div class="container-fluid">
    @include('domain._alerts')
    <h4>{{ $client->company_name }}</h4>
    <p><strong>External ID:</strong> {{ $client->external_id ?: '-' }}</p>
    <p><strong>Contact:</strong> {{ $client->contact_name ?: '-' }} / {{ $client->contact_email ?: '-' }}</p>
    <p><strong>Phone:</strong> {{ $client->contact_phone ?: '-' }}</p>
    <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">Edit</a>
    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Delete</button>
    </form>
</div>
