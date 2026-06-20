<div class="container-fluid">
@include('domain._page_header', ['title' => 'Audit Log Detail', 'breadcrumbs' => ['Security' => null, 'Audit Logs' => route('audit-logs.index'), 'Detail' => null]])

@include('domain._alerts')

@component('domain._card')
<dl class="row">
    <dt class="col-sm-3">Action</dt><dd class="col-sm-9"><code>{{ $auditLog->action ?? $auditLog->event }}</code></dd>
    <dt class="col-sm-3">Hotel</dt><dd class="col-sm-9">{{ $auditLog->hotel?->name ?? $auditLog->hotel_id ?? '-' }}</dd>
    <dt class="col-sm-3">Actor</dt><dd class="col-sm-9">{{ $auditLog->actor?->username ?? $auditLog->actor_id ?? '-' }}</dd>
    <dt class="col-sm-3">Entity</dt><dd class="col-sm-9">{{ $auditLog->entity_type ?? $auditLog->auditable_type ?? '-' }} #{{ $auditLog->entity_id ?? $auditLog->auditable_id ?? '-' }}</dd>
    <dt class="col-sm-3">Request ID</dt><dd class="col-sm-9">{{ $auditLog->request_id ?? '-' }}</dd>
</dl>

@foreach (['before_data' => 'Before', 'after_data' => 'After', 'metadata' => 'Metadata'] as $field => $label)
    <h5>{{ $label }}</h5>
    <pre class="bg-light p-3 border rounded">{{ json_encode($auditLog->{$field} ?: [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
@endforeach

<a href="{{ route('audit-logs.index') }}" class="btn btn-secondary spa_route">Back</a>
@endcomponent
</div>
