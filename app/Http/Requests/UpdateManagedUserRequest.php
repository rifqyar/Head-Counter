<?php

namespace App\Http\Requests;

use App\Support\Security\RoleAuthority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        return $this->user()?->can('manage', $target);
    }

    public function rules(): array
    {
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($target->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'hotel_id' => [
                $this->user()->isSuperAdmin() ? 'nullable' : 'prohibited',
                Rule::exists('hotels', 'id')->where('status', 'ACTIVE'),
            ],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $target = $this->route('user');
            if ($target->isSuperAdmin() && $this->input('status') !== 'ACTIVE' && app(RoleAuthority::class)->activeSuperAdminCount() <= 1) {
                $validator->errors()->add('status', 'The last active super-admin cannot be deactivated.');
            }
        });
    }
}
