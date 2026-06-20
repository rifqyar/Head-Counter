<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Hotel', 'breadcrumbs' => ['Master Data' => null, 'Hotels' => route('hotels.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('hotels.store') }}">
        @csrf
        @include('domain.hotels.form', ['hotel' => $hotel])
        @include('domain._form_actions', ['cancelUrl' => route('hotels.index'), 'submitLabel' => 'Save Hotel'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
