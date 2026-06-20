<?php

namespace App\Http\Requests;

use App\Support\Security\RoleAuthority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = Role::find($this->input('role_id'));

        return $role !== null
            && ($this->user()?->isSuperAdmin() || $this->user()?->can('role.manage'))
            && app(RoleAuthority::class)->canManageProtectedRole($this->user(), $role);
    }

    public function rules(): array
    {
        $manageable = app(RoleAuthority::class)->manageablePermissions($this->user())->pluck('name')->all();

        return [
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($manageable)],
        ];
    }
}
