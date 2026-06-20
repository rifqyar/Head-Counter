<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AuthorizesHotelScopedModels
{
    public function viewAny(User $user): bool
    {
        return $this->hasRequiredPermission($user, 'view') && ($user->hotel_id !== null || $user->isSuperAdmin());
    }

    public function view(User $user, $model): bool
    {
        return $this->hasRequiredPermission($user, 'view') && $this->sameHotel($user, $model);
    }

    public function create(User $user): bool
    {
        return $this->hasRequiredPermission($user, 'create') && ($user->hotel_id !== null || $user->isSuperAdmin());
    }

    public function update(User $user, $model): bool
    {
        return $this->hasRequiredPermission($user, 'update') && $this->sameHotel($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        return $this->hasRequiredPermission($user, 'delete') && $this->sameHotel($user, $model);
    }

    protected function sameHotel(User $user, $model): bool
    {
        return $user->isSuperAdmin() || (int) $user->hotel_id === (int) $model->hotel_id;
    }

    protected function hasRequiredPermission(User $user, string $ability): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $map = property_exists($this, 'permissions') ? $this->permissions : [];
        $permission = $map[$ability] ?? $map['default'] ?? null;

        return $permission === null || $user->can($permission);
    }
}
