<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Edit Package: '.$package->name, 'breadcrumbs' => ['Master Data' => null, 'Packages' => route('packages.index'), $package->name => route('packages.show', $package), 'Edit' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
    <form method="POST" action="{{ route('packages.update', $package) }}">
        @csrf
        @method('PUT')
        @include('domain.packages.form', ['package' => $package])
        @include('domain._form_actions', ['cancelUrl' => route('packages.show', $package), 'submitLabel' => 'Update Package'])
    </form>
    @endcomponent
</div>
@include('domain._datatable')
