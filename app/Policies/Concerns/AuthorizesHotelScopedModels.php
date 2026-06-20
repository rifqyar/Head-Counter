<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AuthorizesHotelScopedModels
{
    public function viewAny(User $user): bool
    {
        return $user->hotel_id !== null || $user->isSuperAdmin();
    }

    public function view(User $user, $model): bool
    {
        return $user->isSuperAdmin() || (int) $user->hotel_id === (int) $model->hotel_id;
    }

    public function create(User $user): bool
    {
        return $user->hotel_id !== null || $user->isSuperAdmin();
    }

    public function update(User $user, $model): bool
    {
        return $this->view($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        return $this->view($user, $model);
    }
}
