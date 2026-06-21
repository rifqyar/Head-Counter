<?php

namespace App\Enums;

enum ReportType: string
{
    case MEETINGS = 'meetings';
    case PARTICIPANTS = 'participants';
    case REDEMPTIONS = 'redemptions';
    case PACKAGE_CONSUMPTION = 'package-consumption';
    case ROOM_UTILIZATION = 'room-utilization';

    public function label(): string
    {
        return match ($this) {
            self::MEETINGS => 'Meeting Report',
            self::PARTICIPANTS => 'Participant Attendance Report',
            self::REDEMPTIONS => 'Redemption Report',
            self::PACKAGE_CONSUMPTION => 'Package Consumption Report',
            self::ROOM_UTILIZATION => 'Room Utilization Report',
        };
    }
}
