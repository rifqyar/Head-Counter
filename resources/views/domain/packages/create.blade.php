<div class="container-fluid">
    @include('domain._alerts')
    <h4>Create Package</h4>
    <form method="POST" action="{{ route('packages.store') }}">
        @csrf
        @include('domain.packages.form', ['package' => $package])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
