@php
    $primaryMeeting = $booking->meetingEvents?->first();
    $assignment = $primaryMeeting?->packageAssignments?->first();
@endphp
<div class="wizard-content booking-wizard-shell">
    <div class="booking-wizard wizard-circle">
        <h6>Package</h6>
        <section>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Package <span class="text-danger">*</span></label>
                    <select name="package_id" class="form-control select2 @error('package_id') is-invalid @enderror" required>
                        <option value="">Choose package</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" @selected((int) old('package_id', $assignment?->package_id) === $package->id)>
                                {{ $package->code }} - {{ $package->name }} ({{ number_format((float) $package->price, 0) }})
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($packages->isEmpty())
                        <small class="form-text text-muted">Create an active package before creating a booking.</small>
                    @endif
                </div>
                <div class="form-group col-md-4">
                    <label>Expected Participants <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="expected_participants" class="form-control @error('expected_participants') is-invalid @enderror" value="{{ old('expected_participants', $primaryMeeting->expected_participants ?? 1) }}" required>
                    @error('expected_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead><tr><th>Package</th><th>Entitlements</th></tr></thead>
                    <tbody>
                    @foreach ($packages as $package)
                        <tr>
                            <td>{{ $package->name }}</td>
                            <td>
                                @forelse ($package->entitlements as $entitlement)
                                    <span class="badge badge-info">{{ $entitlement->entitlement_type->value ?? $entitlement->entitlement_type }} x {{ $entitlement->quantity }}</span>
                                @empty
                                    <span class="text-muted">No entitlements configured</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <h6>Date & Room</h6>
        <section>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Meeting Room <span class="text-danger">*</span></label>
                    <select name="meeting_room_id" class="form-control select2 @error('meeting_room_id') is-invalid @enderror" required>
                        <option value="">Choose room</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((int) old('meeting_room_id', $primaryMeeting->meeting_room_id ?? null) === $room->id)>{{ $room->code }} - {{ $room->name }} (capacity {{ $room->capacity }})</option>
                        @endforeach
                    </select>
                    @error('meeting_room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Event Name <span class="text-danger">*</span></label>
                    <input name="event_name" class="form-control @error('event_name') is-invalid @enderror" value="{{ old('event_name', $primaryMeeting->event_name ?? '') }}" required>
                    @error('event_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4"><label>Event Date <span class="text-danger">*</span></label><input type="date" name="event_date" class="form-control @error('event_date') is-invalid @enderror" value="{{ old('event_date', $primaryMeeting?->event_date?->toDateString()) }}" required>@error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-group col-md-4"><label>Start At <span class="text-danger">*</span></label><input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror" value="{{ old('start_at', $primaryMeeting?->start_at?->format('Y-m-d\TH:i')) }}" required>@error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-group col-md-4"><label>End At <span class="text-danger">*</span></label><input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ old('end_at', $primaryMeeting?->end_at?->format('Y-m-d\TH:i')) }}" required>@error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
        </section>

        <h6>Booking</h6>
        <section>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Booking Number</label>
                    <input name="booking_number" class="form-control @error('booking_number') is-invalid @enderror" value="{{ old('booking_number', $booking->booking_number) }}" placeholder="Auto generated if blank">
                    @error('booking_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-4"><label>External Booking ID</label><input name="external_booking_id" class="form-control @error('external_booking_id') is-invalid @enderror" value="{{ old('external_booking_id', $booking->external_booking_id) }}">@error('external_booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-group col-md-4"><label>Booking Date</label><input type="date" name="booking_date" class="form-control @error('booking_date') is-invalid @enderror" value="{{ old('booking_date', optional($booking->booking_date)->toDateString() ?? now()->toDateString()) }}">@error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Client</label>
                    <select name="client_id" class="form-control select2 @error('client_id') is-invalid @enderror">
                        <option value="">No client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected((int) old('client_id', $booking->client_id) === $client->id)>{{ $client->company_name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-4">
                    <label>Source <span class="text-danger">*</span></label>
                    <select name="booking_source" class="form-control @error('booking_source') is-invalid @enderror" required>
                        @foreach ($sources as $source)
                            <option value="{{ $source }}" @selected(old('booking_source', $booking->booking_source ?? 'DIRECT') === $source)>{{ str_replace('_', ' ', $source) }}</option>
                        @endforeach
                    </select>
                    @error('booking_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        @foreach (['DRAFT', 'CONFIRMED', 'CANCELLED', 'COMPLETED'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $booking->status->value ?? $booking->status ?? 'DRAFT') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $booking->notes) }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </section>
    </div>
</div>
