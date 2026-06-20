<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Hotel: '.$hotel->name, 'breadcrumbs' => ['Master Data' => null, 'Hotels' => route('hotels.index'), $hotel->name => route('hotels.show', $hotel), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('hotels.update', $hotel) }}">
        @csrf
        @method('PUT')
        @include('domain.hotels.form', ['hotel' => $hotel])
        @include('domain._form_actions', ['cancelUrl' => route('hotels.show', $hotel), 'submitLabel' => 'Update Hotel'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
