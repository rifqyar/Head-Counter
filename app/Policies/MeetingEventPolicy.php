<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesHotelScopedModels;

class MeetingEventPolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'meeting.view',
        'create' => 'meeting.create',
        'update' => 'meeting.update',
        'delete' => 'meeting.cancel',
    ];

    public function transition(User $user, $model): bool
    {
        return $this->sameHotel($user, $model)
            && ($user->isSuperAdmin()
                || $user->can('meeting.update')
                || $user->can('meeting.start')
                || $user->can('meeting.complete')
                || $user->can('meeting.cancel')
                || $user->can('attendance.view')
                || $user->can('attendance.scan'));
    }

    public function update(User $user, $model): bool
    {
        return $this->sameHotel($user, $model)
            && ($user->isSuperAdmin()
                || $user->can('meeting.update')
                || $user->can('attendance.view')
                || $user->can('attendance.scan'));
    }
}
