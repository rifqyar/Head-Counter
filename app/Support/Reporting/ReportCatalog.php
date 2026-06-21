<?php

namespace App\Support\Reporting;

use App\Enums\ReportType;

class ReportCatalog
{
    public static function types(): array
    {
        return collect(ReportType::cases())->mapWithKeys(fn (ReportType $type) => [
            $type->value => $type->label(),
        ])->all();
    }

    public static function columns(string $type, bool $includeHotel = false, bool $includeContact = false): array
    {
        $columns = match ($type) {
            ReportType::MEETINGS->value => ['Booking number', 'Client', 'Meeting name', 'Room', 'Date', 'Start time', 'End time', 'Expected participants', 'Actual participants', 'Attendance percentage', 'Package', 'Status'],
            ReportType::PARTICIPANTS->value => array_values(array_filter(['Participant name', 'Company', $includeContact ? 'Contact' : null, 'Registration time', 'Check-in time', 'QR status', 'Meeting', 'Attendance status'])),
            ReportType::REDEMPTIONS->value => ['Participant', 'Meeting', 'Meal session', 'Entitlement type', 'Redemption time', 'Scanner operator', 'Device', 'Result', 'Rejection reason', 'Override information', 'Reversal information'],
            ReportType::PACKAGE_CONSUMPTION->value => ['Meeting', 'Package', 'Expected quantity', 'Registered participants', 'Redeemed quantity', 'Remaining quantity', 'Consumption percentage'],
            ReportType::ROOM_UTILIZATION->value => ['Meeting room', 'Date range', 'Total reserved hours', 'Total occupied hours', 'Utilization percentage', 'Cancellation rate', 'No-show rate'],
            default => [],
        };

        if ($includeHotel) {
            $columns[] = 'Hotel';
        }

        return $columns;
    }
}
