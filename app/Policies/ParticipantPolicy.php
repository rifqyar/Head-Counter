<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesHotelScopedModels;

class ParticipantPolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'participant.view',
        'create' => 'participant.register',
        'update' => 'participant.update',
        'delete' => 'participant.block',
    ];

    public function manageQr(User $user, $participant): bool
    {
        return ($user->isSuperAdmin() || $user->can('participant.qr.manage'))
            && $this->sameHotel($user, $participant);
    }
}
