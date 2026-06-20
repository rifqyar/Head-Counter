<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OverrideRedemptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('redemption.override');
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:1000']];
    }
}
