<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateLegacyClientRequest extends StoreLegacyClientRequest
{
    public function rules(): array
    {
        $clientId = $this->route('id');

        return [
            'code' => ['required', 'string', 'max:3', Rule::unique('m_client', 'code')->ignore($clientId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('m_client', 'name')->ignore($clientId)],
            'contact_person' => ['required', 'string', 'max:255'],
            'company_phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('m_client', 'email')->ignore($clientId)],
        ];
    }
}
