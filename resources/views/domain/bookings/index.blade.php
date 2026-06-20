<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Bookings</h4>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">Create Booking</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>Number</th><th>Client</th><th>Date</th><th>Source</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($bookings as $booking)
            <tr><td>{{ $booking->booking_number }}</td><td>{{ $booking->client?->company_name ?? '-' }}</td><td>{{ $booking->booking_date?->toDateString() }}</td><td>{{ $booking->booking_source }}</td><td>{{ $booking->status->value ?? $booking->status }}</td><td><a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-info">Detail</a> <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-sm btn-warning">Edit</a></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No bookings found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
