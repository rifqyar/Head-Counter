<div class="container-fluid">
    @include('domain._alerts')
    <h4>Register Participant</h4>
    <form method="POST" action="{{ route('participants.store') }}">
        @csrf
        @include('domain.participants.form', ['participant' => $participant])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
