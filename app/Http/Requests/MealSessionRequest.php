<?php

namespace App\Http\Requests;

use App\Enums\EntitlementType;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MealSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('meal_session.manage');
    }

    public function rules(): array
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $this->user()->hotel_id;

        return [
            'meeting_event_id' => ['required', Rule::exists('meeting_events', 'id')->where('hotel_id', $hotelId)],
            'entitlement_type' => ['required', Rule::enum(EntitlementType::class)],
            'session_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', 'in:DRAFT,OPEN,CLOSED,CANCELLED'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
