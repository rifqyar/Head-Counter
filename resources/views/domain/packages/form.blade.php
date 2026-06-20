@php($entitlement = $package->entitlements->first() ?? null)
<div class="form-row">
    <div class="form-group col-md-3"><label>Code</label><input name="code" class="form-control" value="{{ old('code', $package->code) }}" required></div>
    <div class="form-group col-md-5"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $package->name) }}" required></div>
    <div class="form-group col-md-2"><label>Price</label><input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $package->price ?? 0) }}" required></div>
    <div class="form-group col-md-2"><label>Active</label><select name="is_active" class="form-control"><option value="1" @selected(old('is_active', $package->is_active ?? true))>Yes</option><option value="0" @selected(! old('is_active', $package->is_active ?? true))>No</option></select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control">{{ old('description', $package->description) }}</textarea></div>
<div class="form-row">
    <div class="form-group col-md-6"><label>Entitlement Type</label><select name="entitlement_type" class="form-control"><option value="">None</option>@foreach (['COFFEE_BREAK', 'LUNCH', 'DINNER', 'SNACK', 'WELCOME_DRINK', 'CUSTOM'] as $type)<option value="{{ $type }}" @selected(old('entitlement_type', $entitlement?->entitlement_type->value ?? $entitlement?->entitlement_type) === $type)>{{ $type }}</option>@endforeach</select></div>
    <div class="form-group col-md-6"><label>Entitlement Quantity</label><input type="number" min="0" name="entitlement_quantity" class="form-control" value="{{ old('entitlement_quantity', $entitlement?->quantity ?? 1) }}"></div>
</div>
