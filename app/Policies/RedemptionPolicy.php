<?php

namespace App\Policies;

use App\Domain\Redemption\Redemption;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHotelScopedModels;

class RedemptionPolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'redemption.view',
        'update' => 'redemption.override',
        'delete' => 'redemption.reverse',
    ];

    public function override(User $user, Redemption $redemption): bool
    {
        return $this->sameHotel($user, $redemption) && ($user->isSuperAdmin() || $user->can('redemption.override'));
    }

    public function reverse(User $user, Redemption $redemption): bool
    {
        return $this->sameHotel($user, $redemption) && ($user->isSuperAdmin() || $user->can('redemption.reverse'));
    }
}
