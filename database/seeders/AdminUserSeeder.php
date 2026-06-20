<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Platform Super Admin',
                'email' => 'superadmin@headcounter.test',
                'hotel_id' => null,
                'password' => bcrypt('superadmin123456'),
            ]
        );

        $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        $legacyRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user->syncRoles([$role->name, $legacyRole->name]);

        $permission = Permission::all();
        foreach ($permission as $value) {
            $role->givePermissionTo($value->name);
        }

        Role::firstOrCreate(['name' => 'General Manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Hotel Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Front Office', 'guard_name' => 'web']);
    }
}
