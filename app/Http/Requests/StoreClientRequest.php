<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;

        return [
            'hotel_id' => [
                $this->user()->isSuperAdmin() && ! $hotelId ? 'required' : 'nullable',
                'integer',
                Rule::exists('hotels', 'id')->where('status', 'ACTIVE'),
            ],
            'hotel_ids' => ['nullable', 'array'],
            'hotel_ids.*' => ['integer', Rule::exists('hotels', 'id')->where('status', 'ACTIVE')],
            'external_id' => ['nullable', 'max:255', Rule::unique('clients', 'external_id')->where('hotel_id', $hotelId)->ignore($this->route('client'))],
            'company_name' => ['required', 'max:255'],
            'contact_name' => ['nullable', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'max:30'],
            'billing_address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
