<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Booking: {{ $booking->booking_number }}</h4>
    <form method="POST" action="{{ route('bookings.update', $booking) }}">
        @csrf
        @method('PUT')
        @include('domain.bookings.form', ['booking' => $booking])
        <button class="btn btn-primary">Update</button>
    </form>
</div>
