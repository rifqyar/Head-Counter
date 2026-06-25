<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Hotel Settings',
        'breadcrumbs' => ['Setting' => null, 'Hotel Settings' => null],
    ])
    @include('domain._alerts')
    @include('domain._validation_summary')

    @if ($currentHotel)
        @component('domain._card')
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')
                @if (auth()->user()->isSuperAdmin())
                    <input type="hidden" name="hotel_id" value="{{ $currentHotel->id }}">
                @endif
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Hotel Name</label>
                        <input class="form-control" name="name" value="{{ old('name', $currentHotel->name) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Timezone</label>
                        <input class="form-control" name="timezone" value="{{ old('timezone', $currentHotel->timezone) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Default Booking Source</label>
                        <input class="form-control" name="settings[default_booking_source]" value="{{ old('settings.default_booking_source', $currentHotel->settings['default_booking_source'] ?? '') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="address" rows="2">{{ old('address', $currentHotel->address) }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Logo Path</label>
                        <input class="form-control" name="settings[logo_path]" value="{{ old('settings.logo_path', $currentHotel->settings['logo_path'] ?? '') }}" placeholder="images/logo-full.png">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Contact Email</label>
                        <input class="form-control" name="settings[contact_email]" value="{{ old('settings.contact_email', $currentHotel->settings['contact_email'] ?? '') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Contact Phone</label>
                        <input class="form-control" name="settings[contact_phone]" value="{{ old('settings.contact_phone', $currentHotel->settings['contact_phone'] ?? '') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Meeting QR Note</label>
                    <textarea class="form-control" name="settings[meeting_qr_note]" rows="3">{{ old('settings.meeting_qr_note', $currentHotel->settings['meeting_qr_note'] ?? '') }}</textarea>
                </div>
                <button class="btn btn-primary js-disable-on-submit" type="submit">Save Settings</button>
            </form>
        @endcomponent
    @else
        @component('domain._card')
            <p class="mb-2">Super admin is currently viewing all hotels. Switch into a tenant to edit that tenant's operational settings.</p>
            <a href="{{ route('tenant-switch.index') }}" class="btn btn-primary spa_route">Choose Tenant</a>
            <a href="{{ route('settings.subscriptions.index') }}" class="btn btn-outline-info spa_route">Manage Subscriptions</a>
        @endcomponent
    @endif

    @if (auth()->user()->isSuperAdmin() && $hotels->isNotEmpty())
        @component('domain._card')
            <h5 class="mb-3">Tenant Administration</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Hotel</th><th>Status</th><th>Subscription</th><th>Users</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($hotels as $hotel)
                            @php($subscription = $hotel->settings['subscription'] ?? [])
                            <tr>
                                <td>{{ $hotel->code }}<br><small>{{ $hotel->name }}</small></td>
                                <td><span class="badge badge-secondary">{{ $hotel->status->value ?? $hotel->status }}</span></td>
                                <td>{{ $subscription['plan'] ?? 'Unassigned' }} / {{ $subscription['status'] ?? 'TRIAL' }}</td>
                                <td>{{ $hotel->users_count }}</td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-outline-info spa_route" href="{{ route('hotels.show', $hotel) }}">Hotel</a>
                                    <a class="btn btn-sm btn-outline-primary spa_route" href="{{ route('users.index', ['hotel_id' => $hotel->id]) }}">Users</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcomponent
    @endif
</div>
