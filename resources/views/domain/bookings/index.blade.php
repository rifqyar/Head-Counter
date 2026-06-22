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
                <thead><tr><th>Number</th><th>Client</th><th>Date</th><th>Source</th><th>Status</th><th>Meeting QR</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($bookings as $booking)
                    @php($meeting = $booking->meetingEvents->first())
                    <tr>
                        <td>{{ $booking->booking_number }}</td>
                        <td>{{ $booking->client?->company_name ?? '-' }}</td>
                        <td>{{ $booking->booking_date?->toDateString() }}</td>
                        <td>{{ $booking->booking_source }}</td>
                        <td><span class="badge badge-secondary">{{ $booking->status->value ?? $booking->status }}</span></td>
                        <td>
                            @if ($meeting?->meeting_qr_path)
                                <a href="{{ route('meetings.qr.download', $meeting) }}" class="btn btn-sm btn-outline-info">Download PDF</a>
                            @else
                                <span class="text-muted">Not issued</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-info spa_route">Detail</a>
                            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-sm btn-warning spa_route">Edit</a>
                            @if (($booking->status->value ?? $booking->status) !== 'CONFIRMED')
                                <form method="POST" action="{{ route('bookings.status', $booking) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="CONFIRMED">
                                    <button class="btn btn-sm btn-success js-disable-on-submit">Confirm</button>
                                </form>
                            @endif
                            @if (($booking->status->value ?? $booking->status) !== 'CANCELLED')
                                <form method="POST" action="{{ route('bookings.status', $booking) }}" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
                                    @csrf
                                    <input type="hidden" name="status" value="CANCELLED">
                                    <button class="btn btn-sm btn-outline-danger js-disable-on-submit">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No bookings found for the active hotel context.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
