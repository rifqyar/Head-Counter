<?php

namespace App\Support\Security;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAuthority
{
    public const PROTECTED_ROLES = ['SUPER_ADMIN', 'HOTEL_ADMIN', 'SCANNER_OPERATOR', 'AUDITOR', 'Super Admin'];

    public function assignableRoles(User $actor)
    {
        $query = Role::query()->orderBy('name');

        if (! $actor->isSuperAdmin()) {
            $query->whereNotIn('name', ['SUPER_ADMIN', 'Super Admin', 'Administrator']);
        }

        return $query->get();
    }

    public function manageablePermissions(User $actor)
    {
        $query = Permission::query()->orderBy('name');

        if (! $actor->isSuperAdmin()) {
            $query->whereNotIn('name', ['hotel.manage', 'role.manage', 'permission.manage', 'integration.manage']);
        }

        return $query->get();
    }

    public function canAssignRoles(User $actor, array $roleNames): bool
    {
        $allowed = $this->assignableRoles($actor)->pluck('name')->all();

        return collect($roleNames)->every(fn ($role) => in_array($role, $allowed, true));
    }

    public function canManageProtectedRole(User $actor, Role $role): bool
    {
        return ! in_array($role->name, self::PROTECTED_ROLES, true) || $actor->isSuperAdmin();
    }

    public function activeSuperAdminCount(): int
    {
        return User::role(['SUPER_ADMIN', 'Super Admin'])->where(function ($query) {
            $query->whereNull('status')->orWhere('status', 'ACTIVE');
        })->whereNull('deactivated_at')->count();
    }
}
