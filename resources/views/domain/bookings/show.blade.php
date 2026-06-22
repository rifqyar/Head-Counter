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
        <p><strong>Source:</strong> {{ $booking->booking_source }}</p>
        <p><strong>Status:</strong> <span class="badge badge-secondary">{{ $booking->status->value ?? $booking->status }}</span></p>
        <p><strong>Notes:</strong> {{ $booking->notes ?: '-' }}</p>
        @foreach ($booking->meetingEvents as $meeting)
            <hr>
            <h5>{{ $meeting->event_name }}</h5>
            <p><strong>Room:</strong> {{ $meeting->meetingRoom?->name ?? '-' }}</p>
            <p><strong>Schedule:</strong> {{ $meeting->start_at?->format('d M Y H:i') }} - {{ $meeting->end_at?->format('H:i') }}</p>
            <p><strong>Quota:</strong> {{ $meeting->expected_participants }}</p>
            <p><strong>Package:</strong> {{ $meeting->packageAssignments->first()?->package?->name ?? '-' }}</p>
            @if ($meeting->meeting_qr_path)
                <p><strong>Meeting QR:</strong> <span class="badge badge-success">Issued PDF</span> <a href="{{ route('meetings.qr.download', $meeting) }}" class="btn btn-sm btn-outline-info ml-2">Download QR PDF</a></p>
            @else
                <p><strong>Meeting QR:</strong> <span class="badge badge-secondary">Not issued</span></p>
            @endif
        @endforeach
        @if (($booking->status->value ?? $booking->status) !== 'CONFIRMED')
            <form method="POST" action="{{ route('bookings.status', $booking) }}" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="CONFIRMED">
                <button class="btn btn-success js-disable-on-submit">Confirm and Generate QR</button>
            </form>
        @endif
        <form method="POST" action="{{ route('bookings.status', $booking) }}" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
            @csrf
            <input type="hidden" name="status" value="CANCELLED">
            <button class="btn btn-outline-danger js-disable-on-submit">Cancel</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
