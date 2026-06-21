<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('report.view');
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['nullable', 'integer', Rule::exists('hotels', 'id')->where('status', 'ACTIVE')],
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'room_id' => ['nullable', 'integer', 'exists:meeting_rooms,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'meeting_id' => ['nullable', 'integer', 'exists:meeting_events,id'],
            'package_id' => ['nullable', 'integer', 'exists:meeting_packages,id'],
            'meal_session_id' => ['nullable', 'integer', 'exists:meal_sessions,id'],
            'scanner_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'max:40'],
            'attendance_status' => ['nullable', Rule::in(['CHECKED_IN', 'NOT_CHECKED_IN'])],
            'qr_status' => ['nullable', Rule::in(['ACTIVE', 'EXPIRED', 'REVOKED'])],
            'rejection_code' => ['nullable', 'string', 'max:40'],
            'entitlement_type' => ['nullable', 'string', 'max:40'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->user()?->isSuperAdmin()) {
            $this->merge(['hotel_id' => null]);
        }
    }
}
