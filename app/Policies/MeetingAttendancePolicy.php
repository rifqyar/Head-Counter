<?php

namespace App\Policies;

use App\Models\User;

class MeetingAttendancePolicy
{
    public function view(User $user, $attendance): bool
    {
        return ($user->isSuperAdmin() || $user->can('attendance.view'))
            && ($user->isSuperAdmin() || (int) $user->hotel_id === (int) $attendance->meetingEvent->hotel_id);
    }
}
