<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Tenant Context', 'breadcrumbs' => ['Administration' => null, 'Tenant Context' => null]])
    @include('domain._validation_summary')
    @component('domain._card')
            <p class="text-muted mb-3">
                Current context:
                <strong>{{ $currentHotel ? $currentHotel->code.' - '.$currentHotel->name : 'All active hotels' }}</strong>
            </p>
            <form method="POST" action="{{ route('tenant-switch.switch') }}" class="form-inline mb-2">
                @csrf
                <label class="sr-only" for="tenant_hotel_id">Hotel</label>
                <select id="tenant_hotel_id" name="hotel_id" class="form-control select2 mr-2 @error('hotel_id') is-invalid @enderror" required>
                    <option value="">Choose hotel</option>
                    @foreach ($hotels as $hotel)
                        <option value="{{ $hotel->id }}" @selected($currentHotel?->id === $hotel->id)>{{ $hotel->code }} - {{ $hotel->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary js-disable-on-submit" type="submit">Switch Hotel</button>
            </form>
            <form method="POST" action="{{ route('tenant-switch.reset') }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-secondary js-disable-on-submit" type="submit">Reset to all hotels</button>
            </form>
    @endcomponent
</div>
@include('domain._datatable')
