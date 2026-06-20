<?php

namespace App\Http\Requests;

class RedeemParticipantRequest extends ValidateParticipantQRRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'idempotency_key' => ['required', 'string', 'max:120'],
        ]);
    }
}
