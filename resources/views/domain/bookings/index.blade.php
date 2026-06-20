<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Bookings',
        'breadcrumbs' => ['Operations' => null, 'Bookings' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('bookings.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Create Booking</a>'),
    ])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Number</th><th>Client</th><th>Date</th><th>Source</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($bookings as $booking)
                    <tr><td>{{ $booking->booking_number }}</td><td>{{ $booking->client?->company_name ?? '-' }}</td><td>{{ $booking->booking_date?->toDateString() }}</td><td>{{ $booking->booking_source }}</td><td><span class="badge badge-secondary">{{ $booking->status->value ?? $booking->status }}</span></td><td><a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-sm btn-warning spa_route">Edit</a></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No bookings found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
