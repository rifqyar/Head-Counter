<div class="container-fluid">
@include('domain._page_header', ['title' => 'Users', 'breadcrumbs' => ['Security' => null, 'Users' => null], 'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('users.create').'" class="btn btn-primary spa_route">Create User</a>')])
@include('domain._alerts')
@component('domain._card')
<div class="table-responsive">
    <table class="table table-striped">
        <thead><tr><th>Name</th><th>Username</th><th>Hotel</th><th>Status</th><th>Roles</th><th>Tokens</th><th></th></tr></thead>
        <tbody>
        @foreach ($users as $managedUser)
            <tr>
                <td>{{ $managedUser->name }}</td>
                <td>{{ $managedUser->username }}<br><small>{{ $managedUser->email }}</small></td>
                <td>{{ $managedUser->hotel?->code ?? 'Platform' }}</td>
                <td><span class="badge badge-{{ $managedUser->isActive() ? 'success' : 'secondary' }}">{{ $managedUser->status ?? 'ACTIVE' }}</span></td>
                <td>{{ $managedUser->roles->pluck('name')->join(', ') ?: '-' }}</td>
                <td>{{ $managedUser->tokens->count() }}</td>
                <td class="text-right"><a class="btn btn-sm btn-outline-info spa_route" href="{{ route('users.show', $managedUser) }}">Detail</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $users->links() }}
@endcomponent
</div>
