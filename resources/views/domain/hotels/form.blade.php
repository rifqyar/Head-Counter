<div class="form-row">
    <div class="form-group col-md-3">
        <label>Code <span class="text-danger">*</span></label>
        <input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $hotel->code) }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-5">
        <label>Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $hotel->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-2">
        <label>Timezone <span class="text-danger">*</span></label>
        <input name="timezone" class="form-control @error('timezone') is-invalid @enderror" value="{{ old('timezone', $hotel->timezone ?? 'Asia/Jakarta') }}" required>
        @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-2">
        <label>Status</label>
        <select name="status" class="form-control @error('status') is-invalid @enderror">
            @foreach (['ACTIVE', 'INACTIVE'] as $status)
                <option value="{{ $status }}" @selected(old('status', $hotel->status->value ?? $hotel->status ?? 'ACTIVE') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-group">
    <label>Address</label>
    <textarea name="address" class="form-control @error('address') is-invalid @enderror">{{ old('address', $hotel->address) }}</textarea>
    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
