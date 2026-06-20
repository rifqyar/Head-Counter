<?php

namespace App\Http\Requests;

use App\Enums\ParticipantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'max:255'],
            'company_name' => ['nullable', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'max:50'],
            'identity_reference' => ['nullable', 'max:255'],
            'status' => ['required', Rule::enum(ParticipantStatus::class)],
        ];
    }
}
