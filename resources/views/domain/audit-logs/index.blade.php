<div class="container-fluid">
@include('domain._page_header', ['title' => 'Audit Logs', 'breadcrumbs' => ['Security' => null, 'Audit Logs' => null]])

@include('domain._alerts')

@component('domain._card')
<form method="GET" action="{{ route('audit-logs.index') }}" class="mb-3">
    <div class="form-row">
        <div class="col-md-2 mb-2"><input class="form-control" name="hotel_id" value="{{ request('hotel_id') }}" placeholder="Hotel ID"></div>
        <div class="col-md-2 mb-2"><input class="form-control" name="actor_id" value="{{ request('actor_id') }}" placeholder="Actor ID"></div>
        <div class="col-md-2 mb-2"><input class="form-control" name="action" value="{{ request('action') }}" placeholder="Action"></div>
        <div class="col-md-2 mb-2"><input class="form-control" name="entity_type" value="{{ request('entity_type') }}" placeholder="Entity type"></div>
        <div class="col-md-2 mb-2"><input class="form-control" name="request_id" value="{{ request('request_id') }}" placeholder="Request ID"></div>
        <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block" type="submit">Filter</button></div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Time</th>
                <th>Hotel</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Entity</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($auditLogs as $log)
                <tr>
                    <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->hotel?->code ?? $log->hotel_id ?? '-' }}</td>
                    <td>{{ $log->actor?->username ?? $log->actor_id ?? '-' }}</td>
                    <td><code>{{ $log->action ?? $log->event }}</code></td>
                    <td>{{ class_basename($log->entity_type ?? $log->auditable_type) }} #{{ $log->entity_id ?? $log->auditable_id }}</td>
                    <td><a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-info spa_route">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No audit logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $auditLogs->links() }}
@endcomponent
</div>
