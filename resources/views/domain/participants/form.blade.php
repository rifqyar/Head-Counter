@if (! $participant->exists)
    <div class="form-group"><label>Meeting</label><select name="meeting_event_id" class="form-control" required><option value="">Choose meeting</option>@foreach ($meetings as $meeting)<option value="{{ $meeting->id }}" @selected((int) old('meeting_event_id') === $meeting->id)>{{ $meeting->event_name }}</option>@endforeach</select></div>
@endif
<div class="form-row">
    <div class="form-group col-md-6"><label>Full Name</label><input name="full_name" class="form-control" value="{{ old('full_name', $participant->full_name) }}" required></div>
    <div class="form-group col-md-6"><label>Company</label><input name="company_name" class="form-control" value="{{ old('company_name', $participant->company_name) }}"></div>
</div>
<div class="form-row">
    <div class="form-group col-md-4"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $participant->email) }}"></div>
    <div class="form-group col-md-4"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone', $participant->phone) }}"></div>
    <div class="form-group col-md-4"><label>Identity Reference</label><input name="identity_reference" class="form-control" value="{{ old('identity_reference', $participant->identity_reference) }}"></div>
</div>
@if ($participant->exists)
    <div class="form-group"><label>Status</label><select name="status" class="form-control">@foreach (['REGISTERED', 'CHECKED_IN', 'CANCELLED', 'BLOCKED'] as $status)<option value="{{ $status }}" @selected(old('status', $participant->status->value ?? $participant->status) === $status)>{{ $status }}</option>@endforeach</select></div>
@endif
