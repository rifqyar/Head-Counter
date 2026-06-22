@if (! in_array($meetingStatus, ['COMPLETED', 'CANCELLED', 'NO_SHOW'], true))
    <form method="POST" action="{{ route('meetings.transition', $meeting) }}" class="d-inline">
        @csrf
        <select name="status" class="form-control d-inline-block w-auto">
            @foreach (['CHECKIN_OPEN', 'OCCUPIED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'] as $status)
                <option value="{{ $status }}">{{ $status }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary js-disable-on-submit">Transition</button>
    </form>
    <form method="POST" action="{{ route('meetings.destroy', $meeting) }}" class="d-inline" onsubmit="return confirm('Cancel this meeting?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger js-disable-on-submit">Cancel</button>
    </form>
@endif
