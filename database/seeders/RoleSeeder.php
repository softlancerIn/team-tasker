<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Manager', 'slug' => 'manager'],
            ['name' => 'Developer', 'slug' => 'developer'],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        // Assign Admin role to the first user if exists
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $user = \App\Models\User::first();
        if ($user && $adminRole) {
            $user->update([
                'role_id' => $adminRole->id,
                'is_approved' => true,
            ]);
        }
    }
}
