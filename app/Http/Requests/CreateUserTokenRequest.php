<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserTokenRequest extends FormRequest
{
    public const ABILITIES = [
        'scanner:validate',
        'scanner:redeem',
        'meeting:read',
        'participant:read',
        'integration:read',
        'integration:write',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('manage', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(self::ABILITIES)],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
