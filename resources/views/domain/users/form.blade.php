<div class="form-row">
    <div class="form-group col-md-6">
        <label>Name</label>
        <input class="form-control" name="name" value="{{ old('name', $managedUser->name) }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Username</label>
        <input class="form-control" name="username" value="{{ old('username', $managedUser->username) }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Email</label>
        <input class="form-control" name="email" value="{{ old('email', $managedUser->email) }}">
    </div>
    <div class="form-group col-md-6">
        <label>Status</label>
        <select class="form-control" name="status">
            @foreach (['ACTIVE', 'INACTIVE'] as $status)
                <option value="{{ $status }}" @selected(old('status', $managedUser->status ?? 'ACTIVE') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
</div>
@if (auth()->user()->isSuperAdmin())
    <div class="form-group">
        <label>Hotel</label>
        <select class="form-control" name="hotel_id">
            <option value="">Platform user</option>
            @foreach ($hotels as $hotel)
                <option value="{{ $hotel->id }}" @selected((int) old('hotel_id', $managedUser->hotel_id) === (int) $hotel->id)>{{ $hotel->code }} - {{ $hotel->name }}</option>
            @endforeach
        </select>
    </div>
@endif
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Password {{ $managedUser->exists ? '(leave blank to keep)' : '' }}</label>
        <input class="form-control" type="password" name="password" @if(! $managedUser->exists) required @endif>
    </div>
    <div class="form-group col-md-6">
        <label>Confirm Password</label>
        <input class="form-control" type="password" name="password_confirmation" @if(! $managedUser->exists) required @endif>
    </div>
</div>
@if (! $managedUser->exists)
    <div class="form-group">
        <label>Roles</label>
        <select class="form-control" name="roles[]" multiple>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected(collect(old('roles', []))->contains($role->name))>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>
@endif
