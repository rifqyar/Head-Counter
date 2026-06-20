<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateParticipantQRRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $hotelId = $this->user()->isSuperAdmin()
            ? app(TenantContext::class)->hotelId()
            : $this->user()->hotel_id;

        return [
            'qr_token' => ['required', 'string', 'max:255'],
            'meal_session_id' => ['required', Rule::exists('meal_sessions', 'id')->where('hotel_id', $hotelId)],
            'device_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
