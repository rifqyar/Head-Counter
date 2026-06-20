<div class="container-fluid">
    @include('domain._alerts')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Tenant Context</h4>
            <p class="text-muted mb-3">
                Current context:
                <strong>{{ $currentHotel?->name ?? 'All active hotels' }}</strong>
            </p>
            <form method="POST" action="{{ route('tenant-switch.switch') }}" class="form-inline">
                @csrf
                <select name="hotel_id" class="form-control mr-2" required>
                    <option value="">Choose hotel</option>
                    @foreach ($hotels as $hotel)
                        <option value="{{ $hotel->id }}" @selected($currentHotel?->id === $hotel->id)>{{ $hotel->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit">Switch</button>
            </form>
            <form method="POST" action="{{ route('tenant-switch.reset') }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-secondary" type="submit">Reset to all hotels</button>
            </form>
        </div>
    </div>
</div>
