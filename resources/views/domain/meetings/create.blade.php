<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Meeting', 'breadcrumbs' => ['Operations' => null, 'Meetings' => route('meetings.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('meetings.store') }}">
        @csrf
        @include('domain.meetings.form', ['meeting' => $meeting])
        @include('domain._form_actions', ['cancelUrl' => route('meetings.index'), 'submitLabel' => 'Save Meeting'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
