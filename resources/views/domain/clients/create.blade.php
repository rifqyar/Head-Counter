<div class="container-fluid">
    @include('domain._alerts')
    <h4>Create Client</h4>
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        @include('domain.clients.form', ['client' => $client])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
