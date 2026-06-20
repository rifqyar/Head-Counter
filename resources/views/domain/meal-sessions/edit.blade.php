<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Meal Session: '.$session->name, 'breadcrumbs' => ['Operations' => null, 'Meal Sessions' => route('meal-sessions.index'), $session->name => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('meal-sessions.update', $session) }}">
        @csrf
        @method('PUT')
        @include('domain.meal-sessions.form')
        @include('domain._form_actions', ['cancelUrl' => route('meal-sessions.index'), 'submitLabel' => 'Update Meal Session'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
