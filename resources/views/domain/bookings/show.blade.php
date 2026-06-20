<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $booking->booking_number,
        'breadcrumbs' => ['Operations' => null, 'Bookings' => route('bookings.index'), $booking->booking_number => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('bookings.edit', $booking).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        <p><strong>Hotel:</strong> {{ $booking->hotel?->name ?? '-' }}</p>
        <p><strong>Client:</strong> {{ $booking->client?->company_name ?? '-' }}</p>
        <p><strong>Date:</strong> {{ $booking->booking_date?->toDateString() ?: '-' }}</p>
        <p><strong>Status:</strong> <span class="badge badge-secondary">{{ $booking->status->value ?? $booking->status }}</span></p>
        <p><strong>Notes:</strong> {{ $booking->notes ?: '-' }}</p>
        <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger js-disable-on-submit">Cancel</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
