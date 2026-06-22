<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Booking', 'breadcrumbs' => ['Operations' => null, 'Bookings' => route('bookings.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
<form method="POST" action="{{ route('bookings.store') }}" data-cancel-url="{{ route('bookings.index') }}">
        @csrf
        @include('domain.bookings.form', ['booking' => $booking])
        <div class="booking-form-actions">
            @include('domain._form_actions', ['cancelUrl' => route('bookings.index'), 'submitLabel' => 'Save Booking'])
        </div>
    </form>
@endcomponent
</div>
@include('domain._datatable')
@include('domain.bookings._wizard_script')
