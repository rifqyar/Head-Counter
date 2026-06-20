<div class="form-row">
    <div class="form-group col-md-3">
        <label>Code</label>
        <input name="code" class="form-control" value="{{ old('code', $hotel->code) }}" required>
    </div>
    <div class="form-group col-md-5">
        <label>Name</label>
        <input name="name" class="form-control" value="{{ old('name', $hotel->name) }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>Timezone</label>
        <input name="timezone" class="form-control" value="{{ old('timezone', $hotel->timezone ?? 'Asia/Jakarta') }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>Status</label>
        <select name="status" class="form-control">
            @foreach (['ACTIVE', 'INACTIVE'] as $status)
                <option value="{{ $status }}" @selected(old('status', $hotel->status->value ?? $hotel->status ?? 'ACTIVE') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label>Address</label>
    <textarea name="address" class="form-control">{{ old('address', $hotel->address) }}</textarea>
</div>
