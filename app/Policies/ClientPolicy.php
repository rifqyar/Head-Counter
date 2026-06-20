<?php

namespace App\Policies;

use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hotel_id !== null || $user->isSuperAdmin();
    }

    public function view(User $user, $client): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $client->hotels()->where('hotels.id', $user->hotel_id)->exists()
            || (int) $client->hotel_id === (int) $user->hotel_id;
    }

    public function create(User $user): bool
    {
        return $user->hotel_id !== null || $user->isSuperAdmin();
    }

    public function update(User $user, $client): bool
    {
        return $this->view($user, $client);
    }

    public function delete(User $user, $client): bool
    {
        return $this->view($user, $client);
    }
}
