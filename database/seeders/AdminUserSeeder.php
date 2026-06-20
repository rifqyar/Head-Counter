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
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.test',
                'password' => bcrypt('admin123456'),
            ]
        );

        $role = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $user->assignRole($role->name);

        $permission = Permission::all();
        foreach ($permission as $key => $value) {
            $role->givePermissionTo($value->name);
        }
    }
}
