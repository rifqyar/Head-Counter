<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class MeetingRoomPolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'meeting_room.view',
        'create' => 'meeting_room.manage',
        'update' => 'meeting_room.manage',
        'delete' => 'meeting_room.manage',
    ];
}
