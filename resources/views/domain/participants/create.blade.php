<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Register Participant', 'breadcrumbs' => ['Operations' => null, 'Participants' => route('participants.index'), 'Register' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('participants.store') }}">
        @csrf
        @include('domain.participants.form', ['participant' => $participant])
        @include('domain._form_actions', ['cancelUrl' => route('participants.index'), 'submitLabel' => 'Save Participant'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
