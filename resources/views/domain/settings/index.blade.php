<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Hotel Settings',
        'breadcrumbs' => ['Setting' => null, 'Hotel Settings' => null],
    ])
    @include('domain._alerts')
    @include('domain._validation_summary')

    @if ($currentHotel)
        @component('domain._card')
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if (auth()->user()->isSuperAdmin())
                    <input type="hidden" name="hotel_id" value="{{ $currentHotel->id }}">
                @endif
                <div class="hc-settings-grid">
                    <div class="hc-settings-section">
                        <div class="hc-settings-section-title">Hotel Identity</div>
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
                        <div class="form-group mb-0">
                            <label>Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ old('address', $currentHotel->address) }}</textarea>
                        </div>
                    </div>

                    <div class="hc-settings-section">
                        <div class="hc-settings-section-title">Branding & Contact</div>
                        <div class="form-row">
                            <div class="form-group col-lg-5">
                                <label>Hotel Logo</label>
                                @php($currentLogoPath = $currentHotel->settings['logo_path'] ?? null)
                                <div class="hc-logo-upload">
                                    <div class="hc-logo-preview">
                                        @if ($currentLogoPath)
                                            <img src="{{ Storage::disk('public')->exists($currentLogoPath) ? Storage::url($currentLogoPath) : asset($currentLogoPath) }}" alt="Current Logo">
                                        @else
                                            <span class="hc-logo-preview-empty">No logo uploaded</span>
                                        @endif
                                    </div>
                                    <div class="flex-fill">
                                        @if ($currentLogoPath)
                                            <small class="form-text text-muted mb-2">Current: {{ $currentLogoPath }}</small>
                                        @endif
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('logo_file') is-invalid @enderror" id="logo_file" name="logo_file" accept="image/*">
                                            <label class="custom-file-label" for="logo_file">Choose image file&hellip;</label>
                                        </div>
                                        @error('logo_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">JPG, PNG, GIF, SVG. Max 2 MB. Leave empty to keep current logo.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-3">
                                <label>Contact Email</label>
                                <input class="form-control" name="settings[contact_email]" value="{{ old('settings.contact_email', $currentHotel->settings['contact_email'] ?? '') }}">
                            </div>
                            <div class="form-group col-lg-4">
                                <label>Contact Phone</label>
                                <input class="form-control" name="settings[contact_phone]" value="{{ old('settings.contact_phone', $currentHotel->settings['contact_phone'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="hc-settings-section">
                        <div class="hc-settings-section-title">QR Document Note</div>
                        <div class="form-group mb-0">
                            <label>Meeting QR Note</label>
                            <textarea class="form-control" name="settings[meeting_qr_note]" rows="3">{{ old('settings.meeting_qr_note', $currentHotel->settings['meeting_qr_note'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary js-disable-on-submit" type="submit"><i class="mdi mdi-content-save"></i> Save Settings</button>
                </div>
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
@include('domain._datatable')
<script>
    $(function () {
        $('#logo_file').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').text(fileName || 'Choose image file\u2026');
        });
    });
</script>
