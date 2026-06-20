<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Room: {{ $room->name }}</h4>
    <form method="POST" action="{{ route('meeting-rooms.update', $room) }}">
        @csrf
        @method('PUT')
        @include('domain.meeting-rooms.form', ['room' => $room])
        <button class="btn btn-primary">Update</button>
    </form>
</div>
