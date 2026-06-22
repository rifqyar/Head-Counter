<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Booking: '.$booking->booking_number, 'breadcrumbs' => ['Operations' => null, 'Bookings' => route('bookings.index'), $booking->booking_number => route('bookings.show', $booking), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('bookings.update', $booking) }}" data-cancel-url="{{ route('bookings.show', $booking) }}">
        @csrf
        @method('PUT')
        @include('domain.bookings.form', ['booking' => $booking])
        <div class="booking-form-actions">
            @include('domain._form_actions', ['cancelUrl' => route('bookings.show', $booking), 'submitLabel' => 'Update Booking'])
        </div>
    </form>
@endcomponent
</div>
@include('domain._datatable')
@include('domain.bookings._wizard_script')
