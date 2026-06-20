<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmParticipantQRRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('participant.qr.manage');
    }

    public function rules(): array
    {
        return ['confirm' => ['accepted']];
    }
}
