<?php

namespace App\Http\Requests;

use App\Support\Security\RoleAuthority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', $this->route('user'));
    }

    public function rules(): array
    {
        $assignableRoles = app(RoleAuthority::class)->assignableRoles($this->user())->pluck('name')->all();

        return [
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in($assignableRoles)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $target = $this->route('user');
            $roles = $this->input('roles', []);

            if ($target->isSuperAdmin() && ! collect($roles)->intersect(['SUPER_ADMIN', 'Super Admin'])->isNotEmpty() && app(RoleAuthority::class)->activeSuperAdminCount() <= 1) {
                $validator->errors()->add('roles', 'The last active super-admin role assignment cannot be removed.');
            }
        });
    }
}
