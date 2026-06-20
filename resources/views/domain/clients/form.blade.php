<div class="form-row">
    <div class="form-group col-md-3"><label>External ID</label><input name="external_id" class="form-control" value="{{ old('external_id', $client->external_id) }}"></div>
    <div class="form-group col-md-5"><label>Company Name</label><input name="company_name" class="form-control" value="{{ old('company_name', $client->company_name) }}" required></div>
    <div class="form-group col-md-4"><label>Contact Name</label><input name="contact_name" class="form-control" value="{{ old('contact_name', $client->contact_name) }}"></div>
</div>
<div class="form-row">
    <div class="form-group col-md-6"><label>Email</label><input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $client->contact_email) }}"></div>
    <div class="form-group col-md-6"><label>Phone</label><input name="contact_phone" class="form-control" value="{{ old('contact_phone', $client->contact_phone) }}"></div>
</div>
<div class="form-group"><label>Billing Address</label><textarea name="billing_address" class="form-control">{{ old('billing_address', $client->billing_address) }}</textarea></div>
<div class="form-group"><label>Tax Number</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $client->tax_number) }}"></div>
