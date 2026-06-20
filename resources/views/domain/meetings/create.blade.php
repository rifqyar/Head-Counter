<div class="container-fluid">
    @include('domain._alerts')
    <h4>Create Meeting</h4>
    <form method="POST" action="{{ route('meetings.store') }}">
        @csrf
        @include('domain.meetings.form', ['meeting' => $meeting])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
