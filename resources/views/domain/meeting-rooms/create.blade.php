<div class="container-fluid">
    @include('domain._alerts')
    <h4>Create Meeting Room</h4>
    <form method="POST" action="{{ route('meeting-rooms.store') }}">
        @csrf
        @include('domain.meeting-rooms.form', ['room' => $room])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
