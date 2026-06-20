<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateLegacyMeetingScheduleRequest extends StoreLegacyMeetingScheduleRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('meeting.update') || $this->user()?->can('Meeting Schedule');
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'id' => ['required', 'integer', Rule::exists('trx_meeting_schedule', 'id')],
            'code_client' => ['sometimes', Rule::exists('m_client', 'code')],
            'package' => ['sometimes', Rule::exists('m_packages', 'kd_pck')],
        ]);
    }
}
