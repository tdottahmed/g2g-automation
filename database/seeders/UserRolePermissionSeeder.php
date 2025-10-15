<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions for roles
        Permission::create(['name' => 'create role', 'group' => 'Role Management']);
        Permission::create(['name' => 'view role', 'group' => 'Role Management']);
        Permission::create(['name' => 'update role', 'group' => 'Role Management']);
        Permission::create(['name' => 'delete role', 'group' => 'Role Management']);

        // Create Permissions for users
        Permission::create(['name' => 'create user', 'group' => 'User Management']);
        Permission::create(['name' => 'view user', 'group' => 'User Management']);
        Permission::create(['name' => 'update user', 'group' => 'User Management']);
        Permission::create(['name' => 'delete user', 'group' => 'User Management']);

        // Create Permissions for permissions
        Permission::create(['name' => 'create permission', 'group' => 'Permission Management']);
        Permission::create(['name' => 'view permission', 'group' => 'Permission Management']);
        Permission::create(['name' => 'update permission', 'group' => 'Permission Management']);
        Permission::create(['name' => 'delete permission', 'group' => 'Permission Management']);

        // Create Roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $staffRole = Role::create(['name' => 'staff']);
        $userRole = Role::create(['name' => 'user']);

        // Assign all permissions to super-admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to admin
        $adminRole->givePermissionTo([
            'create role',
            'view role',
            'update role',
            'create user',
            'view user',
            'update user',
            'create permission',
            'view permission'
        ]);

        // Create Users and Assign Roles
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        $superAdminUser->assignRole($superAdminRole);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        $adminUser->assignRole($adminRole);

        $staffUser = User::firstOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'name' => 'Staff',
                'password' => Hash::make('12345678'),
            ]
        );
        $staffUser->assignRole($staffRole);
    }
}
