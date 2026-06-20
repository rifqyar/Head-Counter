<?php

namespace App\Enums;

enum ParticipantStatus: string
{
    case REGISTERED = 'REGISTERED';
    case CHECKED_IN = 'CHECKED_IN';
    case CANCELLED = 'CANCELLED';
    case BLOCKED = 'BLOCKED';
}
