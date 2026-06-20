<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'meeting_event_id' => ['required', Rule::exists('meeting_events', 'id')->where('hotel_id', app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id)],
            'full_name' => ['required', 'max:255'],
            'company_name' => ['nullable', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'max:50'],
            'identity_reference' => ['nullable', 'max:255'],
            'registration_source' => ['nullable', 'max:255'],
        ];
    }
}
