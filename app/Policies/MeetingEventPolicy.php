<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesHotelScopedModels;

class MeetingEventPolicy
{
    use AuthorizesHotelScopedModels;

    public function transition(User $user, $model): bool
    {
        return $this->update($user, $model);
    }
}
