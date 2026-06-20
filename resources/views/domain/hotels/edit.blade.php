<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Hotel: {{ $hotel->name }}</h4>
    <form method="POST" action="{{ route('hotels.update', $hotel) }}">
        @csrf
        @method('PUT')
        @include('domain.hotels.form', ['hotel' => $hotel])
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('hotels.show', $hotel) }}" class="btn btn-secondary">Back</a>
    </form>
</div>
