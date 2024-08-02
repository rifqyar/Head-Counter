<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Permission::create(['name' => 'Dashboard']);
        Permission::create(['name' => 'Master Data']);
        Permission::create(['name' => 'Transaction']);
        Permission::create(['name' => 'Report']);
        Permission::create(['name' => 'Setting']);

        // Child Menu Permission
            // Master Data
        Permission::create(['name' => 'Meeting Schedule']);
        Permission::create(['name' => 'meeting.add']);
        Permission::create(['name' => 'Client']);
        Permission::create(['name' => 'client.add']);

            // Transaction
        Permission::create(['name' => 'Meeting Trans']);
        Permission::create(['name' => 'Additional Slot']);

            // Report
        Permission::create(['name' => 'Meeting Report']);

            // Setting
        Permission::create(['name' => 'Manage User']);
        Permission::create(['name' => 'Manage Role']);
        Permission::create(['name' => 'Manage Permission']);
    }
}
