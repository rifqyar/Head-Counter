<div class="container-fluid">
    @include('domain._page_header', ['title' => 'Reports', 'breadcrumbs' => ['Reports' => null]])
    <div class="row">
        @foreach ($reports as $key => $label)
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ $label }}</h4>
                        <a href="{{ route('reports.show', $key) }}" class="btn btn-primary spa_route">Open report</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
