<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateParticipantQRRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('redemption.scan') ?? false;
    }

    public function rules(): array
    {
        return [
            'qr_token' => ['required', 'string', 'max:255'],
            'meal_session_id' => ['required', Rule::exists('meal_sessions', 'id')->where('hotel_id', app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id)],
            'device_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
