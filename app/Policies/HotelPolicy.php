<?php

namespace App\Policies;

use App\Domain\Hotel\Hotel;
use App\Models\User;

class HotelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Hotel $hotel): bool
    {
        return $user->isSuperAdmin() || (int) $user->hotel_id === (int) $hotel->id;
    }

    public function manage(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
