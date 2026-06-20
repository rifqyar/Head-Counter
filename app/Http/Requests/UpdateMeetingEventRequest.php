<?php

namespace App\Http\Requests;

class UpdateMeetingEventRequest extends StoreMeetingEventRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['status']);

        return $rules;
    }
}
