<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Room: '.$room->name, 'breadcrumbs' => ['Master Data' => null, 'Meeting Rooms' => route('meeting-rooms.index'), $room->name => route('meeting-rooms.show', $room), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('meeting-rooms.update', $room) }}">
        @csrf
        @method('PUT')
        @include('domain.meeting-rooms.form', ['room' => $room, 'hotels' => $hotels, 'currentHotel' => $currentHotel])
        @include('domain._form_actions', ['cancelUrl' => route('meeting-rooms.show', $room), 'submitLabel' => 'Update Room'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
