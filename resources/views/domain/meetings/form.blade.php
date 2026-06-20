<div class="form-row">
    <div class="form-group col-md-6"><label>Event Name</label><input name="event_name" class="form-control" value="{{ old('event_name', $meeting->event_name) }}" required></div>
    <div class="form-group col-md-3"><label>Event Date</label><input type="date" name="event_date" class="form-control" value="{{ old('event_date', optional($meeting->event_date)->toDateString()) }}" required></div>
    <div class="form-group col-md-3">
        <label>Status</label>
        @if ($meeting->exists)
            <input class="form-control" value="{{ $meeting->status->value ?? $meeting->status }}" disabled>
        @else
            <select name="status" class="form-control">@foreach (['DRAFT', 'SCHEDULED'] as $status)<option value="{{ $status }}" @selected(old('status', $meeting->status->value ?? $meeting->status ?? 'DRAFT') === $status)>{{ $status }}</option>@endforeach</select>
        @endif
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6"><label>Start At</label><input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', $meeting->start_at?->format('Y-m-d\TH:i')) }}" required></div>
    <div class="form-group col-md-6"><label>End At</label><input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', $meeting->end_at?->format('Y-m-d\TH:i')) }}" required></div>
</div>
<div class="form-row">
    <div class="form-group col-md-4"><label>Booking</label><select name="booking_id" class="form-control"><option value="">No booking</option>@foreach ($bookings as $booking)<option value="{{ $booking->id }}" @selected((int) old('booking_id', $meeting->booking_id) === $booking->id)>{{ $booking->booking_number }}</option>@endforeach</select></div>
    <div class="form-group col-md-4"><label>Room</label><select name="meeting_room_id" class="form-control"><option value="">No room</option>@foreach ($rooms as $room)<option value="{{ $room->id }}" @selected((int) old('meeting_room_id', $meeting->meeting_room_id) === $room->id)>{{ $room->code }} - {{ $room->name }}</option>@endforeach</select></div>
    <div class="form-group col-md-4"><label>Expected Participants</label><input type="number" min="0" name="expected_participants" class="form-control" value="{{ old('expected_participants', $meeting->expected_participants ?? 0) }}" required></div>
</div>
<input type="hidden" name="actual_participants" value="{{ old('actual_participants', $meeting->actual_participants ?? 0) }}">
