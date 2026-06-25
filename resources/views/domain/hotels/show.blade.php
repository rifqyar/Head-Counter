<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => $hotel->name,
        'breadcrumbs' => ['Master Data' => null, 'Hotels' => route('hotels.index'), $hotel->name => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('hotels.edit', $hotel).'" class="btn btn-warning spa_route">Edit</a>'),
    ])
    @component('domain._card')
        @php($subscription = $hotel->settings['subscription'] ?? [])
        <p><strong>Code:</strong> {{ $hotel->code }}</p>
        <p><strong>Address:</strong> {{ $hotel->address ?: '-' }}</p>
        <p><strong>Timezone:</strong> {{ $hotel->timezone }}</p>
        <p><strong>Status:</strong> <span class="badge badge-secondary">{{ $hotel->status->value ?? $hotel->status }}</span></p>
        <p><strong>Subscription:</strong> {{ $subscription['plan'] ?? 'Unassigned' }} / {{ $subscription['status'] ?? 'TRIAL' }}</p>
        <p><strong>Subscription Expiry:</strong> {{ $subscription['expires_at'] ?? '-' }}</p>
        <p><strong>Users:</strong> {{ $hotel->users()->count() }}</p>
        @if (auth()->user()?->isSuperAdmin())
            <a href="{{ route('users.index', ['hotel_id' => $hotel->id]) }}" class="btn btn-outline-primary spa_route">Manage Users</a>
            <a href="{{ route('settings.subscriptions.index') }}" class="btn btn-outline-info spa_route">Manage Subscription</a>
        @endif
        <form method="POST" action="{{ route('hotels.destroy', $hotel) }}" class="d-inline" onsubmit="return confirm('Deactivate this hotel?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger js-disable-on-submit">Deactivate</button>
        </form>
    @endcomponent
</div>
@include('domain._datatable')
