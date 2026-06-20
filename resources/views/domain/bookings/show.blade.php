<div class="container-fluid">
    @include('domain._alerts')
    <h4>{{ $booking->booking_number }}</h4>
    <p><strong>Client:</strong> {{ $booking->client?->company_name ?? '-' }}</p>
    <p><strong>Date:</strong> {{ $booking->booking_date?->toDateString() ?: '-' }}</p>
    <p><strong>Status:</strong> {{ $booking->status->value ?? $booking->status }}</p>
    <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-warning">Edit</a>
    <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Cancel</button>
    </form>
</div>
