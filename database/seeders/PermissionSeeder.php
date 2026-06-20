<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main Menu Permission
        Permission::firstOrCreate(['name' => 'Dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Master Data', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Transaction', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Report', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Setting', 'guard_name' => 'web']);

        // Child Menu Permission
        // Master Data
        Permission::firstOrCreate(['name' => 'Meeting Schedule', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'meeting.add', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Client', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'client.add', 'guard_name' => 'web']);

        // Transaction
        Permission::firstOrCreate(['name' => 'Meeting Trans', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Additional Slot', 'guard_name' => 'web']);

        // Report
        Permission::firstOrCreate(['name' => 'Meeting Report', 'guard_name' => 'web']);

        // Setting
        Permission::firstOrCreate(['name' => 'Manage User', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Manage Role', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Manage Permission', 'guard_name' => 'web']);

        // Phase 3 domain permissions.
        foreach ([
            'Hotel',
            'Meeting Room',
            'Booking',
            'Meeting Event',
            'Meeting Package',
            'Participant',
            'Meeting Attendance',
            'Tenant Switch',
            'meeting.qr.manage',
            'participant.qr.manage',
            'meal_session.view',
            'meal_session.manage',
            'redemption.view',
            'redemption.scan',
            'redemption.override',
            'redemption.reverse',
        ] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
