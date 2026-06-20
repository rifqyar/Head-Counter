<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Meeting: {{ $meeting->event_name }}</h4>
    <form method="POST" action="{{ route('meetings.update', $meeting) }}">
        @csrf
        @method('PUT')
        @include('domain.meetings.form', ['meeting' => $meeting])
        <button class="btn btn-primary">Update</button>
    </form>
</div>
