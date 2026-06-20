<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Booking', 'breadcrumbs' => ['Operations' => null, 'Bookings' => route('bookings.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf
        @include('domain.bookings.form', ['booking' => $booking])
        @include('domain._form_actions', ['cancelUrl' => route('bookings.index'), 'submitLabel' => 'Save Booking'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
