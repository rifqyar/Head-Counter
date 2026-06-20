<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Participant: '.$participant->full_name, 'breadcrumbs' => ['Operations' => null, 'Participants' => route('participants.index'), $participant->full_name => route('participants.show', $participant), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('participants.update', $participant) }}">
        @csrf
        @method('PUT')
        @include('domain.participants.form', ['participant' => $participant])
        @include('domain._form_actions', ['cancelUrl' => route('participants.show', $participant), 'submitLabel' => 'Update Participant'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
