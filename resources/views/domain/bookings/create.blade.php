<div class="container-fluid">
    @include('domain._alerts')
    <h4>Create Booking</h4>
    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf
        @include('domain.bookings.form', ['booking' => $booking])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
