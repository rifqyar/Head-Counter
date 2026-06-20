<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegacyMeetingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('meeting.create') || $this->user()?->can('Meeting Schedule');
    }

    public function rules(): array
    {
        return [
            'code_client' => ['required', Rule::exists('m_client', 'code')],
            'tgl_start' => ['required', 'date'],
            'tgl_end' => ['required', 'date', 'after_or_equal:tgl_start'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'kuota' => ['required', 'integer', 'min:1', 'max:100000'],
            'package' => ['required', Rule::exists('m_packages', 'kd_pck')],
            'rooms' => ['required', 'array', 'min:1', 'max:1'],
            'rooms.*' => ['required', Rule::exists('m_meeting_rooms', 'kd_room')],
        ];
    }
}
