<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $user = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('admin123456')
        ]);

        $role = Role::create(['name' => 'Administrator']);
        $user->assignRole($role->name);

        $role = Role::where('name', 'Administrator')->first();
        $permission = Permission::all();
        foreach ($permission as $key => $value) {
            $role->givePermissionTo($value->name);
        }
    }
}
