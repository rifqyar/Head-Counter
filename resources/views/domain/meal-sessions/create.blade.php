<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Meal Session', 'breadcrumbs' => ['Operations' => null, 'Meal Sessions' => route('meal-sessions.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('meal-sessions.store') }}">
        @csrf
        @include('domain.meal-sessions.form')
        @include('domain._form_actions', ['cancelUrl' => route('meal-sessions.index'), 'submitLabel' => 'Save Meal Session'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
