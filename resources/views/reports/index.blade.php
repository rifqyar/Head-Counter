<div class="container-fluid">
    @include('domain._page_header', ['title' => 'Reports', 'breadcrumbs' => ['Reports' => null]])
    <div class="row">
        @foreach ($reports as $key => $label)
            @php
                $icon = match ($key) {
                    'meetings' => 'mdi-calendar-check',
                    'participants' => 'mdi-account-multiple',
                    'redemptions' => 'mdi-qrcode-scan',
                    'package-consumption' => 'mdi-package-variant',
                    'room-utilization' => 'mdi-domain',
                    default => 'mdi-chart-bar',
                };
            @endphp
            <div class="col-md-4">
                <div class="card hc-report-card">
                    <div class="card-body">
                        <span class="hc-report-icon"><i class="mdi {{ $icon }}"></i></span>
                        <h4 class="card-title">{{ $label }}</h4>
                        <a href="{{ route('reports.show', $key) }}" class="btn btn-primary spa_route">
                            <i class="mdi mdi-open-in-new"></i>
                            Open report
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
