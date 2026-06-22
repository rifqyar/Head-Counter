@if (! $meeting->exists)
    @php($selectedBooking = $bookings->firstWhere('id', (int) old('booking_id')))
    <div class="alert alert-info">Meeting schedules are now processed from booking data. Select one unprocessed draft booking to continue.</div>
    <div class="form-group">
        <label>Booking <span class="text-danger">*</span></label>
        <select name="booking_id" id="meeting-booking-select" class="form-control select2" required>
            <option value="">Choose booking</option>
            @foreach ($bookings as $booking)
                @php($bookingMeeting = $booking->meetingEvents->sortBy('start_at')->first())
                <option value="{{ $booking->id }}"
                    data-event-name="{{ $bookingMeeting?->event_name }}"
                    data-event-date="{{ $bookingMeeting?->event_date?->toDateString() }}"
                    data-start-at="{{ $bookingMeeting?->start_at?->format('Y-m-d\TH:i') }}"
                    data-end-at="{{ $bookingMeeting?->end_at?->format('Y-m-d\TH:i') }}"
                    data-room="{{ $bookingMeeting?->meetingRoom?->name }}"
                    data-package="{{ $bookingMeeting?->packageAssignments->first()?->package?->name }}"
                    data-expected="{{ $bookingMeeting?->expected_participants }}"
                    data-status="{{ $bookingMeeting?->status->value ?? $bookingMeeting?->status ?? 'DRAFT' }}"
                    @selected((int) old('booking_id') === $booking->id)>
                    {{ $booking->booking_number }} - {{ $booking->client?->company_name ?? 'No client' }} ({{ $booking->status->value ?? $booking->status }})
                </option>
            @endforeach
        </select>
        @if ($bookings->isEmpty())
            <small class="form-text text-muted">No draft booking is waiting to be processed for meeting attendance.</small>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-3">
            <tbody>
                <tr><th style="width: 180px">Meeting</th><td id="booking-preview-event">-</td></tr>
                <tr><th>Schedule</th><td id="booking-preview-schedule">-</td></tr>
                <tr><th>Room</th><td id="booking-preview-room">-</td></tr>
                <tr><th>Package</th><td id="booking-preview-package">-</td></tr>
                <tr><th>Expected Participants</th><td id="booking-preview-expected">-</td></tr>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="actual_participants" value="{{ old('actual_participants', 0) }}">
    <script>
        (function () {
            function updateMeetingBookingPreview() {
                var option = $('#meeting-booking-select option:selected');
                var eventName = option.data('event-name') || '-';
                var startAt = option.data('start-at') || '';
                var endAt = option.data('end-at') || '';

                $('#booking-preview-event').text(eventName);
                $('#booking-preview-schedule').text(startAt && endAt ? startAt.replace('T', ' ') + ' - ' + endAt.replace('T', ' ') : '-');
                $('#booking-preview-room').text(option.data('room') || '-');
                $('#booking-preview-package').text(option.data('package') || '-');
                $('#booking-preview-expected').text(option.data('expected') || '-');
            }

            $(updateMeetingBookingPreview);
            $(document).off('change.meetingBooking').on('change.meetingBooking', '#meeting-booking-select', updateMeetingBookingPreview);
        })();
    </script>
@else
    <div class="form-row">
        <div class="form-group col-md-6"><label>Event Name</label><input name="event_name" class="form-control" value="{{ old('event_name', $meeting->event_name) }}" required></div>
        <div class="form-group col-md-3"><label>Event Date</label><input type="date" name="event_date" class="form-control" value="{{ old('event_date', optional($meeting->event_date)->toDateString()) }}" required></div>
        <div class="form-group col-md-3">
            <label>Status</label>
            <input class="form-control" value="{{ $meeting->status->value ?? $meeting->status }}" disabled>
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
@endif
