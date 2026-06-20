<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Client: '.$client->company_name, 'breadcrumbs' => ['Master Data' => null, 'Clients' => route('clients.index'), $client->company_name => route('clients.show', $client), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')
        @include('domain.clients.form', ['client' => $client, 'hotels' => $hotels, 'currentHotel' => $currentHotel])
        @include('domain._form_actions', ['cancelUrl' => route('clients.show', $client), 'submitLabel' => 'Update Client'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
