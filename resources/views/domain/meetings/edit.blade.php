<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Meeting: '.$meeting->event_name, 'breadcrumbs' => ['Operations' => null, 'Meetings' => route('meetings.index'), $meeting->event_name => route('meetings.show', $meeting), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('meetings.update', $meeting) }}">
        @csrf
        @method('PUT')
        @include('domain.meetings.form', ['meeting' => $meeting])
        @include('domain._form_actions', ['cancelUrl' => route('meetings.show', $meeting), 'submitLabel' => 'Update Meeting'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
