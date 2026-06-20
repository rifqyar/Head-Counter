@if (auth()->user()?->isSuperAdmin())
    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Hotel <span class="text-danger">*</span></label>
        <div class="col-sm-9">
            <select name="hotel_id" class="form-control select2 @error('hotel_id') is-invalid @enderror" {{ $room->exists && $room->meetings()->exists() ? 'disabled' : '' }}>
                <option value="">Choose hotel</option>
                @foreach ($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected((int) old('hotel_id', $room->hotel_id ?: $currentHotel?->id) === $hotel->id)>{{ $hotel->code }} - {{ $hotel->name }}</option>
                @endforeach
            </select>
            @if ($room->exists && $room->meetings()->exists())
                <input type="hidden" name="hotel_id" value="{{ $room->hotel_id }}">
                <small class="form-text text-muted">Hotel is locked because this room already has meetings.</small>
            @else
                <small class="form-text text-muted">Super admins assign rooms to one active hotel.</small>
            @endif
            @error('hotel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
@else
    <div class="alert alert-info">Room will be created for {{ $currentHotel?->name ?? auth()->user()?->hotel?->name ?? 'your active hotel' }}.</div>
@endif
<div class="form-row">
    <div class="form-group col-md-3"><label>Code <span class="text-danger">*</span></label><input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $room->code) }}" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-4"><label>Name <span class="text-danger">*</span></label><input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $room->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-2"><label>Floor</label><input name="floor" class="form-control @error('floor') is-invalid @enderror" value="{{ old('floor', $room->floor) }}">@error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-3"><label>Capacity <span class="text-danger">*</span></label><input type="number" min="0" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', $room->capacity ?? 0) }}" required>@error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>
<div class="form-group">
    <label>Status <span class="text-danger">*</span></label>
    <select name="operational_status" class="form-control @error('operational_status') is-invalid @enderror">
        @foreach (['AVAILABLE', 'RESERVED', 'OCCUPIED', 'CLEANING', 'MAINTENANCE', 'INACTIVE'] as $status)
            <option value="{{ $status }}" @selected(old('operational_status', $room->operational_status->value ?? $room->operational_status ?? 'AVAILABLE') === $status)>{{ $status }}</option>
        @endforeach
    </select>
    @error('operational_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
