<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'can-view-ip-addresses',
            'can-create-ip-addresses',
            'can-edit-ip-addresses',
            'can-edit-any-ip-address',
            'can-delete-ip-addresses',
            'can-view-audit-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $regularRole = Role::create(['name' => 'regular']);
        $regularRole->givePermissionTo([
            'can-view-ip-addresses',
            'can-create-ip-addresses',
            'can-edit-ip-addresses',
        ]);

        $adminRole = Role::create(['name' => 'super-admin']);
        $adminRole->givePermissionTo(Permission::all());

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('super-admin');

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('regular');
    }
}
