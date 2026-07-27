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
        // Create 3 Standard Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $procurementRole = Role::firstOrCreate(['name' => 'procurement']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@asset.nfe'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'password' => Hash::make('2528'),
            ]
        );

        // Assign Admin Role
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
