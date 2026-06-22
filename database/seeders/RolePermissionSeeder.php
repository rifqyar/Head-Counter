<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const ROLES = [
        'SUPER_ADMIN',
        'HOTEL_ADMIN',
        'SALES_ADMIN',
        'BANQUET_ADMIN',
        'FRONT_OFFICE',
        'MEETING_OPERATOR',
        'SCANNER_OPERATOR',
        'REPORT_VIEWER',
        'AUDITOR',
    ];

    public const PERMISSIONS = [
        'hotel.manage',
        'user.manage',
        'role.manage',
        'permission.manage',
        'meeting_room.view',
        'meeting_room.manage',
        'client.view',
        'client.manage',
        'booking.view',
        'booking.create',
        'booking.update',
        'booking.cancel',
        'meeting.view',
        'meeting.create',
        'meeting.update',
        'meeting.assign_room',
        'meeting.start',
        'meeting.complete',
        'meeting.cancel',
        'participant.view',
        'participant.register',
        'participant.update',
        'participant.block',
        'attendance.view',
        'attendance.scan',
        'meal_package.view',
        'meal_package.manage',
        'meal_session.view',
        'meal_session.manage',
        'redemption.view',
        'redemption.scan',
        'redemption.override',
        'redemption.reverse',
        'report.view',
        'report.export',
        'audit.view',
        'integration.manage',
        'settings.manage',
        'meeting.qr.manage',
        'participant.qr.manage',
    ];

    public const LEGACY_COMPATIBILITY_PERMISSIONS = [
        'Dashboard',
        'Master Data',
        'Transaction',
        'Report',
        'Setting',
        'Hotel',
        'Meeting Room',
        'Client',
        'Booking',
        'Meeting Schedule',
        'Meeting Event',
        'Meeting Package',
        'Participant',
        'Meeting Attendance',
        'Meeting Trans',
        'Meeting Report',
        'Tenant Switch',
        'Manage User',
        'Manage Role',
        'Manage Permission',
        'client.add',
        'meeting.add',
        'Additional Slot',
    ];

    public const MATRIX = [
        'SUPER_ADMIN' => ['*'],
        'HOTEL_ADMIN' => [
            'meeting_room.view', 'meeting_room.manage',
            'client.view', 'client.manage',
            'booking.view', 'booking.create', 'booking.update', 'booking.cancel',
            'meeting.view', 'meeting.create', 'meeting.update', 'meeting.assign_room', 'meeting.start', 'meeting.complete', 'meeting.cancel',
            'participant.view', 'participant.register', 'participant.update', 'participant.block',
            'attendance.view', 'attendance.scan',
            'meal_package.view', 'meal_package.manage',
            'meal_session.view', 'meal_session.manage',
            'redemption.view', 'redemption.scan', 'redemption.override', 'redemption.reverse',
            'report.view', 'report.export',
            'audit.view',
            'user.manage',
            'settings.manage',
            'meeting.qr.manage', 'participant.qr.manage',
        ],
        'SALES_ADMIN' => [
            'client.view', 'client.manage',
            'booking.view', 'booking.create', 'booking.update', 'booking.cancel',
            'meeting.view', 'meeting.create', 'meeting.update',
            'meal_package.view',
            'participant.view',
        ],
        'BANQUET_ADMIN' => [
            'meeting_room.view',
            'meeting.view', 'meeting.update', 'meeting.assign_room', 'meeting.start', 'meeting.complete', 'meeting.cancel',
            'participant.view', 'participant.update',
            'meal_package.view', 'meal_package.manage',
            'meal_session.view', 'meal_session.manage',
            'redemption.view', 'redemption.override', 'redemption.reverse',
        ],
        'FRONT_OFFICE' => [
            'meeting.view',
            'participant.view', 'participant.register', 'participant.update',
            'attendance.view', 'attendance.scan',
        ],
        'MEETING_OPERATOR' => [
            'meeting.view', 'meeting.update', 'meeting.start', 'meeting.complete',
            'participant.view', 'participant.register', 'participant.update',
            'attendance.view', 'attendance.scan',
        ],
        'SCANNER_OPERATOR' => [
            'meeting.view',
            'participant.view',
            'meal_session.view',
            'redemption.view', 'redemption.scan',
        ],
        'REPORT_VIEWER' => [
            'report.view', 'report.export',
            'meeting.view', 'participant.view', 'attendance.view', 'redemption.view',
        ],
        'AUDITOR' => [
            'audit.view',
            'redemption.view',
            'meeting.view',
            'participant.view',
            'attendance.view',
        ],
    ];

    private const LEGACY_ROLE_ALIASES = [
        'Super Admin' => 'SUPER_ADMIN',
        'Administrator' => 'SUPER_ADMIN',
        'Hotel Admin' => 'HOTEL_ADMIN',
        'General Manager' => 'HOTEL_ADMIN',
        'Front Office' => 'FRONT_OFFICE',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach (array_merge(self::PERMISSIONS, self::LEGACY_COMPATIBILITY_PERMISSIONS) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        foreach (self::ROLES as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->syncPermissions($this->expandedPermissions($roleName));
        }

        foreach (self::LEGACY_ROLE_ALIASES as $legacyRole => $canonicalRole) {
            $role = Role::firstOrCreate(['name' => $legacyRole, 'guard_name' => $guard]);
            $permissions = $this->expandedPermissions($canonicalRole);
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function expandedPermissions(string $roleName): array
    {
        if (self::MATRIX[$roleName] === ['*']) {
            return array_merge(self::PERMISSIONS, self::LEGACY_COMPATIBILITY_PERMISSIONS);
        }

        return array_values(array_unique(array_merge(
            self::MATRIX[$roleName],
            $this->legacyPermissionsFor($roleName)
        )));
    }

    private function legacyPermissionsFor(string $roleName): array
    {
        return match ($roleName) {
            'HOTEL_ADMIN' => ['Dashboard', 'Master Data', 'Transaction', 'Report', 'Setting', 'Hotel', 'Meeting Room', 'Client', 'Booking', 'Meeting Schedule', 'Meeting Event', 'Meeting Package', 'Participant', 'Meeting Attendance', 'Meeting Trans', 'Meeting Report', 'Tenant Switch', 'Manage User'],
            'SALES_ADMIN' => ['Dashboard', 'Master Data', 'Client', 'Booking', 'Meeting Schedule', 'Meeting Event', 'client.add', 'meeting.add'],
            'BANQUET_ADMIN' => ['Dashboard', 'Master Data', 'Transaction', 'Meeting Room', 'Meeting Schedule', 'Meeting Event', 'Meeting Package', 'Participant', 'Meeting Trans'],
            'FRONT_OFFICE' => ['Dashboard', 'Transaction', 'Participant', 'Meeting Attendance', 'Meeting Trans'],
            'MEETING_OPERATOR' => ['Dashboard', 'Transaction', 'Meeting Schedule', 'Meeting Event', 'Participant', 'Meeting Attendance', 'Meeting Trans'],
            'SCANNER_OPERATOR' => ['Dashboard', 'Transaction', 'Meeting Attendance', 'Meeting Trans'],
            'REPORT_VIEWER' => ['Dashboard', 'Report', 'Meeting Report'],
            'AUDITOR' => ['Dashboard', 'Report', 'Meeting Report'],
            default => [],
        };
    }
}
