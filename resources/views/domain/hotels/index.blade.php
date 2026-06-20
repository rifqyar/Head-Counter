<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Hotels</h4>
        <a href="{{ route('hotels.create') }}" class="btn btn-primary">Create Hotel</a>
    </div>
    <table class="table table-striped canonical-datatable">
        <thead><tr><th>Code</th><th>Name</th><th>Timezone</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            @forelse ($hotels as $hotel)
                <tr>
                    <td>{{ $hotel->code }}</td>
                    <td>{{ $hotel->name }}</td>
                    <td>{{ $hotel->timezone }}</td>
                    <td><span class="badge badge-{{ ($hotel->status->value ?? $hotel->status) === 'ACTIVE' ? 'success' : 'secondary' }}">{{ $hotel->status->value ?? $hotel->status }}</span></td>
                    <td>
                        <a href="{{ route('hotels.show', $hotel) }}" class="btn btn-sm btn-info">Detail</a>
                        <a href="{{ route('hotels.edit', $hotel) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No hotels found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('domain._datatable')
