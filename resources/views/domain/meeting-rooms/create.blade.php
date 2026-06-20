<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Meeting Room', 'breadcrumbs' => ['Master Data' => null, 'Meeting Rooms' => route('meeting-rooms.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('meeting-rooms.store') }}">
        @csrf
        @include('domain.meeting-rooms.form', ['room' => $room, 'hotels' => $hotels, 'currentHotel' => $currentHotel])
        @include('domain._form_actions', ['cancelUrl' => route('meeting-rooms.index'), 'submitLabel' => 'Save Room'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
