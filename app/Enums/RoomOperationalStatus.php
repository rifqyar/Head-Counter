<?php

namespace App\Enums;

enum RoomOperationalStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case RESERVED = 'RESERVED';
    case OCCUPIED = 'OCCUPIED';
    case CLEANING = 'CLEANING';
    case MAINTENANCE = 'MAINTENANCE';
    case INACTIVE = 'INACTIVE';
}
