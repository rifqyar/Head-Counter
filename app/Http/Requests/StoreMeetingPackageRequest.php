<?php

namespace App\Http\Requests;

use App\Enums\EntitlementType;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeetingPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'max:30', Rule::unique('meeting_packages', 'code')->where('hotel_id', app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id)->ignore($this->route('package'))],
            'name' => ['required', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'entitlements' => ['nullable', 'array'],
            'entitlements.*.type' => ['nullable', Rule::enum(EntitlementType::class)],
            'entitlements.*.quantity' => ['nullable', 'integer', 'min:0'],
            'entitlements.*.notes' => ['nullable', 'string', 'max:255'],
            'entitlement_type' => ['nullable', Rule::enum(EntitlementType::class)],
            'entitlement_quantity' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
