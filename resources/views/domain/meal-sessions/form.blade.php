<div class="form-row">
    <div class="form-group col-md-6"><label>Meeting</label><select name="meeting_event_id" class="form-control">@foreach ($meetings as $meeting)<option value="{{ $meeting->id }}" @selected(old('meeting_event_id', $session->meeting_event_id) == $meeting->id)>{{ $meeting->event_name }}</option>@endforeach</select></div>
    <div class="form-group col-md-6"><label>Entitlement Type</label><select name="entitlement_type" class="form-control">@foreach ($types as $type)<option value="{{ $type->value }}" @selected(old('entitlement_type', $session->entitlement_type?->value ?? $session->entitlement_type) === $type->value)>{{ $type->value }}</option>@endforeach</select></div>
    <div class="form-group col-md-3"><label>Session Number</label><input type="number" min="1" name="session_number" class="form-control" value="{{ old('session_number', $session->session_number ?? 1) }}"></div>
    <div class="form-group col-md-5"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $session->name) }}"></div>
    <div class="form-group col-md-4"><label>Status</label><select name="status" class="form-control">@foreach (['DRAFT','OPEN','CLOSED','CANCELLED'] as $status)<option value="{{ $status }}" @selected(old('status', $session->status?->value ?? $session->status ?? 'DRAFT') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="form-group col-md-4"><label>Starts At</label><input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $session->starts_at?->format('Y-m-d\\TH:i')) }}"></div>
    <div class="form-group col-md-4"><label>Ends At</label><input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $session->ends_at?->format('Y-m-d\\TH:i')) }}"></div>
    <div class="form-group col-md-4"><label>Location</label><input name="location" class="form-control" value="{{ old('location', $session->location) }}"></div>
</div>
<button class="btn btn-primary">Save</button>
