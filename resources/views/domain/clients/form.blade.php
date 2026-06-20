@if (auth()->user()?->isSuperAdmin())
    <div class="form-group">
        <label>Associated Hotels <span class="text-danger">*</span></label>
        <select name="hotel_ids[]" class="form-control select2 @error('hotel_ids') is-invalid @enderror" multiple>
            @foreach ($hotels as $hotel)
                <option value="{{ $hotel->id }}" @selected(collect(old('hotel_ids', $client->exists ? $client->hotels->pluck('id')->all() : [$currentHotel?->id]))->filter()->map(fn ($id) => (int) $id)->contains($hotel->id))>{{ $hotel->code }} - {{ $hotel->name }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">Clients can be shared by multiple hotels. Bookings only show clients associated with the active hotel.</small>
        @error('hotel_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@else
    <div class="alert alert-info">Client will be associated with {{ $currentHotel?->name ?? auth()->user()?->hotel?->name ?? 'your active hotel' }}.</div>
@endif
<div class="form-row">
    <div class="form-group col-md-3"><label>External ID</label><input name="external_id" class="form-control @error('external_id') is-invalid @enderror" value="{{ old('external_id', $client->external_id) }}">@error('external_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-5"><label>Company Name <span class="text-danger">*</span></label><input name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $client->company_name) }}" required>@error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-4"><label>Contact Name</label><input name="contact_name" class="form-control @error('contact_name') is-invalid @enderror" value="{{ old('contact_name', $client->contact_name) }}">@error('contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>
<div class="form-row">
    <div class="form-group col-md-6"><label>Email</label><input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $client->contact_email) }}">@error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group col-md-6"><label>Phone</label><input name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone', $client->contact_phone) }}">@error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>
<div class="form-group"><label>Billing Address</label><textarea name="billing_address" class="form-control @error('billing_address') is-invalid @enderror">{{ old('billing_address', $client->billing_address) }}</textarea>@error('billing_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-group"><label>Tax Number</label><input name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number', $client->tax_number) }}">@error('tax_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
