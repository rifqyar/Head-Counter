<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Client', 'breadcrumbs' => ['Master Data' => null, 'Clients' => route('clients.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        @include('domain.clients.form', ['client' => $client, 'hotels' => $hotels, 'currentHotel' => $currentHotel])
        @include('domain._form_actions', ['cancelUrl' => route('clients.index'), 'submitLabel' => 'Save Client'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
