<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Package: {{ $package->name }}</h4>
    <form method="POST" action="{{ route('packages.update', $package) }}">
        @csrf
        @method('PUT')
        @include('domain.packages.form', ['package' => $package])
        <button class="btn btn-primary">Update</button>
    </form>
</div>
