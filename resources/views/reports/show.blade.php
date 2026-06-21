<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => $report->label(), 'breadcrumbs' => ['Reports' => route('reports.index'), $report->label() => null]])

    @component('domain._card')
        <form method="GET" action="{{ route('reports.show', $report->value) }}" class="row">
            @if ($filters['hotels']->isNotEmpty())
                <div class="col-md-3 form-group"><label>Hotel</label><select name="hotel_id" class="form-control"><option value="">All authorized hotels</option>@foreach ($filters['hotels'] as $hotel)<option value="{{ $hotel->id }}" @selected(($activeFilters['hotel_id'] ?? null) == $hotel->id)>{{ $hotel->name }}</option>@endforeach</select></div>
            @endif
            <div class="col-md-2 form-group"><label>From</label><input type="date" name="date_from" value="{{ $activeFilters['date_from'] ?? now()->toDateString() }}" class="form-control"></div>
            <div class="col-md-2 form-group"><label>To</label><input type="date" name="date_to" value="{{ $activeFilters['date_to'] ?? now()->toDateString() }}" class="form-control"></div>
            <div class="col-md-2 form-group"><label>Room</label><select name="room_id" class="form-control"><option value="">All rooms</option>@foreach ($filters['rooms'] as $room)<option value="{{ $room->id }}" @selected(($activeFilters['room_id'] ?? null) == $room->id)>{{ $room->name }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label>Meeting</label><select name="meeting_id" class="form-control"><option value="">All meetings</option>@foreach ($filters['meetings'] as $meeting)<option value="{{ $meeting->id }}" @selected(($activeFilters['meeting_id'] ?? null) == $meeting->id)>{{ $meeting->event_name }}</option>@endforeach</select></div>
            <div class="col-md-1 form-group d-flex align-items-end"><button class="btn btn-primary btn-block">Filter</button></div>
        </form>
        @can('report.export')
            <form method="POST" action="{{ route('reports.export', $report->value) }}" class="form-inline">
                @csrf
                @foreach ($activeFilters as $key => $value)
                    @if (filled($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <select name="format" class="form-control mr-2"><option value="xlsx">Excel</option><option value="csv">CSV</option><option value="pdf">PDF</option></select>
                <button class="btn btn-success">Export</button>
                <a href="{{ route('reports.exports.index') }}" class="btn btn-link spa_route">Export center</a>
            </form>
        @endcan
    @endcomponent

    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped table-sm canonical-datatable">
                <thead><tr>@foreach ($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>@foreach ($row as $value)<td>{{ $value }}</td>@endforeach</tr>
                    @empty
                        <tr><td colspan="{{ count($headings) }}" class="text-center text-muted">No report rows for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</div>
@include('domain._datatable')
