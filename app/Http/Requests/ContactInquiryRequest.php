<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'hotel' => ['nullable', 'string', 'max:150'],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'plan' => ['nullable', 'string', 'in:starter,professional,enterprise,general'],
            'type' => ['nullable', 'string', 'in:contact,register'],
            'consent' => ['accepted'],
            'hp_field' => ['nullable', 'string', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Please accept the privacy notice to continue.',
            'hp_field.max' => 'Spam detected.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('type', 'contact'),
            'plan' => $this->input('plan', 'general'),
        ]);
    }
}
