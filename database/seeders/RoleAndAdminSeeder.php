<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $officerRole = Role::firstOrCreate(['name' => 'Procurement Officer']);
        $userRole = Role::firstOrCreate(['name' => 'General User']);

        // Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@asset.nfe'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'password' => Hash::make('2528'),
            ]
        );

        // Assign Role
        $admin->assignRole($superAdminRole);
    }
}
