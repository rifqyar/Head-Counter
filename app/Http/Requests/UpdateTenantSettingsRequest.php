<?php

namespace App\Http\Requests;

use App\Domain\Hotel\Hotel;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => [
                $this->user()->isSuperAdmin() ? 'nullable' : 'prohibited',
                Rule::exists('hotels', 'id'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'timezone' => ['required', 'timezone'],
            'settings' => ['nullable', 'array'],
            'settings.logo_path' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg', 'max:2048'],
            'settings.contact_email' => ['nullable', 'email', 'max:255'],
            'settings.contact_phone' => ['nullable', 'string', 'max:50'],
            'settings.meeting_qr_note' => ['nullable', 'string', 'max:500'],
            'settings.default_booking_source' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()->isSuperAdmin()) {
                return;
            }

            $hotelId = app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;
            if (! $hotelId || ! Hotel::whereKey($hotelId)->exists()) {
                $validator->errors()->add('hotel_id', 'No active tenant hotel is available for settings.');
            }
        });
    }
}
