<div class="container-fluid">
    @include('domain._alerts')
    <h4>Edit Participant: {{ $participant->full_name }}</h4>
    <form method="POST" action="{{ route('participants.update', $participant) }}">
        @csrf
        @method('PUT')
        @include('domain.participants.form', ['participant' => $participant])
        <button class="btn btn-primary">Update</button>
    </form>
</div>
