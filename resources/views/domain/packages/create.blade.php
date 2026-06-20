<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Create Package', 'breadcrumbs' => ['Master Data' => null, 'Packages' => route('packages.index'), 'Create' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('packages.store') }}">
        @csrf
        @include('domain.packages.form', ['package' => $package])
        @include('domain._form_actions', ['cancelUrl' => route('packages.index'), 'submitLabel' => 'Save Package'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
