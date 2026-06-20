<div class="container-fluid">
    @include('domain._alerts')
    <h4>Create Hotel</h4>
    <form method="POST" action="{{ route('hotels.store') }}">
        @csrf
        @include('domain.hotels.form', ['hotel' => $hotel])
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('hotels.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
