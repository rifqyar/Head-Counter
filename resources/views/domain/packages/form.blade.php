@php
    $oldEntitlements = old('entitlements');
    $entitlements = collect($oldEntitlements ?? $package->entitlements->map(fn ($item) => [
        'type' => $item->entitlement_type->value ?? $item->entitlement_type,
        'quantity' => $item->quantity,
        'notes' => $item->metadata['notes'] ?? '',
    ])->all());

    if ($entitlements->isEmpty()) {
        $entitlements = collect([
            ['type' => 'COFFEE_BREAK', 'quantity' => 1, 'notes' => 'Morning coffee break'],
            ['type' => 'LUNCH', 'quantity' => 1, 'notes' => 'Buffet lunch'],
        ]);
    }
@endphp
<div class="form-row">
    <div class="form-group col-md-3"><label>Code</label><input name="code" class="form-control" value="{{ old('code', $package->code) }}" required></div>
    <div class="form-group col-md-5"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $package->name) }}" required></div>
    <div class="form-group col-md-2"><label>Price</label><input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $package->price ?? 0) }}" required></div>
    <div class="form-group col-md-2"><label>Active</label><select name="is_active" class="form-control"><option value="1" @selected(old('is_active', $package->is_active ?? true))>Yes</option><option value="0" @selected(! old('is_active', $package->is_active ?? true))>No</option></select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control">{{ old('description', $package->description) }}</textarea></div>

<label>Entitlements</label>
<div class="table-responsive">
    <table class="table table-bordered" id="entitlements-table">
        <thead><tr><th style="width: 35%">Type</th><th style="width: 20%">Quantity</th><th>Notes</th><th style="width: 1%"></th></tr></thead>
        <tbody>
        @foreach ($entitlements as $index => $entitlement)
            <tr>
                <td>
                    <select name="entitlements[{{ $index }}][type]" class="form-control">
                        <option value="">None</option>
                        @foreach (['COFFEE_BREAK', 'LUNCH', 'DINNER', 'SNACK', 'WELCOME_DRINK', 'CUSTOM'] as $type)
                            <option value="{{ $type }}" @selected(($entitlement['type'] ?? '') === $type)>{{ str_replace('_', ' ', $type) }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" min="0" name="entitlements[{{ $index }}][quantity]" class="form-control" value="{{ $entitlement['quantity'] ?? 1 }}"></td>
                <td><input name="entitlements[{{ $index }}][notes]" class="form-control" value="{{ $entitlement['notes'] ?? '' }}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-entitlement">&times;</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<button type="button" class="btn btn-sm btn-outline-primary" id="add-entitlement">Add Entitlement</button>

<script>
    (function () {
        var nextIndex = {{ $entitlements->count() }};
        var types = ['COFFEE_BREAK', 'LUNCH', 'DINNER', 'SNACK', 'WELCOME_DRINK', 'CUSTOM'];

        function row(index) {
            var options = '<option value="">None</option>' + types.map(function (type) {
                return '<option value="' + type + '">' + type.replace(/_/g, ' ') + '</option>';
            }).join('');

            return '<tr><td><select name="entitlements[' + index + '][type]" class="form-control">' + options + '</select></td>'
                + '<td><input type="number" min="0" name="entitlements[' + index + '][quantity]" class="form-control" value="1"></td>'
                + '<td><input name="entitlements[' + index + '][notes]" class="form-control"></td>'
                + '<td><button type="button" class="btn btn-sm btn-outline-danger js-remove-entitlement">&times;</button></td></tr>';
        }

        $(document).off('click.packageEntitlements').on('click.packageEntitlements', '#add-entitlement', function () {
            $('#entitlements-table tbody').append(row(nextIndex++));
        }).on('click.packageEntitlements', '.js-remove-entitlement', function () {
            if ($('#entitlements-table tbody tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    })();
</script>
