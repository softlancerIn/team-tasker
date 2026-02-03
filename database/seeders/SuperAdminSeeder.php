<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure Admin role exists
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );

        // Create Super Admin User
        User::updateOrCreate(
            ['email' => 'admin@teamtasker.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'), // You can change this later
                'role_id' => $adminRole->id,
                'is_approved' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
