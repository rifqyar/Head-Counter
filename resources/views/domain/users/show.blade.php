<div class="container-fluid">
@include('domain._page_header', ['title' => 'User Detail', 'breadcrumbs' => ['Security' => null, 'Users' => route('users.index'), 'Detail' => null]])
@include('domain._alerts')
@if (session('plainTextToken'))
    <div class="alert alert-warning"><strong>Plain text token, shown once:</strong><pre class="mb-0">{{ session('plainTextToken') }}</pre></div>
@endif
@component('domain._card')
<dl class="row">
    <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $managedUser->name }}</dd>
    <dt class="col-sm-3">Username</dt><dd class="col-sm-9">{{ $managedUser->username }}</dd>
    <dt class="col-sm-3">Hotel</dt><dd class="col-sm-9">{{ $managedUser->hotel?->name ?? 'Platform' }}</dd>
    <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $managedUser->status ?? 'ACTIVE' }}</dd>
    <dt class="col-sm-3">Roles</dt><dd class="col-sm-9">{{ $managedUser->roles->pluck('name')->join(', ') ?: '-' }}</dd>
</dl>
<a href="{{ route('users.edit', $managedUser) }}" class="btn btn-warning spa_route">Edit</a>
@if ($managedUser->isActive())
    <form method="POST" action="{{ route('users.deactivate', $managedUser) }}" class="d-inline">@csrf<button class="btn btn-secondary">Deactivate</button></form>
@else
    <form method="POST" action="{{ route('users.activate', $managedUser) }}" class="d-inline">@csrf<button class="btn btn-success">Activate</button></form>
@endif
@endcomponent

@component('domain._card')
<h5>Role Assignment</h5>
<form method="POST" action="{{ route('users.roles.sync', $managedUser) }}">
    @csrf
    <select class="form-control select2 mb-2" name="roles[]" multiple>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected($managedUser->roles->pluck('name')->contains($role->name))>{{ $role->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary">Save Roles</button>
</form>
@endcomponent

@component('domain._card')
<h5>Effective Permissions</h5>
<div class="d-flex flex-wrap">
    @foreach ($effectivePermissions as $permission)
        <span class="badge badge-light border mr-1 mb-1">{{ $permission }}</span>
    @endforeach
</div>
@endcomponent

@component('domain._card')
<h5>API Tokens</h5>
<form method="POST" action="{{ route('users.tokens.store', $managedUser) }}" class="mb-3">
    @csrf
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6"><input class="form-control" name="name" placeholder="Token name" required></div>
        <div class="form-group col-lg-5 col-md-6">
            <select class="form-control select2" name="abilities[]" multiple required>
                @foreach ($tokenAbilities as $ability)
                    <option value="{{ $ability }}">{{ $ability }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-lg-2 col-md-6"><input class="form-control" type="datetime-local" name="expires_at"></div>
        <div class="form-group col-lg-2 col-md-6"><button class="btn btn-primary btn-block" type="submit">Create Token</button></div>
    </div>
</form>
<table class="table table-sm">
    <thead><tr><th>Name</th><th>Abilities</th><th>Last Used</th><th>Expires</th><th></th></tr></thead>
    <tbody>
    @foreach ($managedUser->tokens as $token)
        <tr>
            <td>{{ $token->name }}</td>
            <td>{{ collect($token->abilities)->join(', ') }}</td>
            <td>{{ optional($token->last_used_at)->format('Y-m-d H:i') ?? '-' }}</td>
            <td>{{ optional($token->expires_at)->format('Y-m-d H:i') ?? '-' }}</td>
            <td class="text-right"><form method="POST" action="{{ route('users.tokens.destroy', [$managedUser, $token]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Revoke</button></form></td>
        </tr>
    @endforeach
    </tbody>
</table>
<form method="POST" action="{{ route('users.tokens.destroy-all', $managedUser) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">Revoke All Tokens</button></form>
@endcomponent
</div>
