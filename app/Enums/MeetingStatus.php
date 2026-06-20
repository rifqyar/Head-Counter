<?php

namespace App\Enums;

enum MeetingStatus: string
{
    case DRAFT = 'DRAFT';
    case SCHEDULED = 'SCHEDULED';
    case CHECKIN_OPEN = 'CHECKIN_OPEN';
    case OCCUPIED = 'OCCUPIED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case NO_SHOW = 'NO_SHOW';
}
