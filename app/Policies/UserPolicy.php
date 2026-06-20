<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('user.manage');
    }

    public function manage(User $user, User $target): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->can('user.manage')
            && $user->hotel_id !== null
            && (int) $user->hotel_id === (int) $target->hotel_id
            && ! $target->isSuperAdmin();
    }
}
