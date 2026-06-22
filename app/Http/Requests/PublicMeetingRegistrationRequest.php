<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicMeetingRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'required_without_all:phone,identity_reference', 'email', 'max:255'],
            'phone' => ['nullable', 'required_without_all:email,identity_reference', 'string', 'max:50'],
            'identity_reference' => ['nullable', 'required_without_all:email,phone', 'string', 'max:255'],
        ];
    }
}
