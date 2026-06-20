<div class="form-row">
    <div class="form-group col-md-3"><label>Code</label><input name="code" class="form-control" value="{{ old('code', $room->code) }}" required></div>
    <div class="form-group col-md-4"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $room->name) }}" required></div>
    <div class="form-group col-md-2"><label>Floor</label><input name="floor" class="form-control" value="{{ old('floor', $room->floor) }}"></div>
    <div class="form-group col-md-3"><label>Capacity</label><input type="number" min="0" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity ?? 0) }}" required></div>
</div>
<div class="form-group">
    <label>Status</label>
    <select name="operational_status" class="form-control">
        @foreach (['AVAILABLE', 'RESERVED', 'OCCUPIED', 'CLEANING', 'MAINTENANCE', 'INACTIVE'] as $status)
            <option value="{{ $status }}" @selected(old('operational_status', $room->operational_status->value ?? $room->operational_status ?? 'AVAILABLE') === $status)>{{ $status }}</option>
        @endforeach
    </select>
</div>
