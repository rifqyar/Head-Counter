<div class="form-row">
    <div class="form-group col-md-4"><label>Booking Number</label><input name="booking_number" class="form-control" value="{{ old('booking_number', $booking->booking_number) }}" required></div>
    <div class="form-group col-md-4"><label>External Booking ID</label><input name="external_booking_id" class="form-control" value="{{ old('external_booking_id', $booking->external_booking_id) }}"></div>
    <div class="form-group col-md-4"><label>Booking Date</label><input type="date" name="booking_date" class="form-control" value="{{ old('booking_date', optional($booking->booking_date)->toDateString()) }}"></div>
</div>
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Client</label>
        <select name="client_id" class="form-control">
            <option value="">No client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected((int) old('client_id', $booking->client_id) === $client->id)>{{ $client->company_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4"><label>Source</label><input name="booking_source" class="form-control" value="{{ old('booking_source', $booking->booking_source ?? 'DIRECT') }}" required></div>
    <div class="form-group col-md-4">
        <label>Status</label>
        <select name="status" class="form-control">
            @foreach (['DRAFT', 'CONFIRMED', 'CANCELLED', 'COMPLETED'] as $status)
                <option value="{{ $status }}" @selected(old('status', $booking->status->value ?? $booking->status ?? 'DRAFT') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group"><label>Notes</label><textarea name="notes" class="form-control">{{ old('notes', $booking->notes) }}</textarea></div>
