<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegacyClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('client.manage') || $this->user()?->can('Client');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:3', Rule::unique('m_client', 'code')],
            'name' => ['required', 'string', 'max:255', Rule::unique('m_client', 'name')],
            'contact_person' => ['required', 'string', 'max:255'],
            'company_phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('m_client', 'email')],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Code Client sudah ada',
            'name.unique' => 'Nama Client sudah ada',
            'code.max' => 'Code Client tidak boleh lebih dari :max karakter',
        ];
    }
}
