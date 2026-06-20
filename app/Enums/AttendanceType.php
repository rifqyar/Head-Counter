<?php

namespace App\Enums;

enum AttendanceType: string
{
    case MEETING_CHECKIN = 'MEETING_CHECKIN';
    case MEETING_CHECKOUT = 'MEETING_CHECKOUT';
}
