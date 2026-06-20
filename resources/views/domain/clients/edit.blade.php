<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Client: {{ $client->company_name }}</h4>
    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')
        @include('domain.clients.form', ['client' => $client])
        <button class="btn btn-primary">Update</button>
    </form>
</div>
