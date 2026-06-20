<div class="form-row">
    <div class="form-group col-md-4"><label>Booking Number <span class="text-danger">*</span></label><input name="booking_number" class="form-control @error('booking_number') is-invalid @enderror" value="{{ old('booking_number', $booking->booking_number) }}" required>@error('booking_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-4"><label>External Booking ID</label><input name="external_booking_id" class="form-control @error('external_booking_id') is-invalid @enderror" value="{{ old('external_booking_id', $booking->external_booking_id) }}">@error('external_booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-4"><label>Booking Date</label><input type="date" name="booking_date" class="form-control @error('booking_date') is-invalid @enderror" value="{{ old('booking_date', optional($booking->booking_date)->toDateString()) }}">@error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
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
        @if ($clients->isEmpty())
            <small class="form-text text-muted">No clients are associated with the active hotel yet. Create or associate a client first.</small>
        @endif
    </div>
    <div class="form-group col-md-4"><label>Source <span class="text-danger">*</span></label><input name="booking_source" class="form-control @error('booking_source') is-invalid @enderror" value="{{ old('booking_source', $booking->booking_source ?? 'DIRECT') }}" required>@error('booking_source')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
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
