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
        $allPermissions = [];
        foreach (config('permissions') as $group => $permissions) {
            if (is_array($permissions)) {
                foreach ($permissions as $action => $label) {
                    $allPermissions[] = "$group.$action";
                }
            } else {
                $allPermissions[] = $group;
            }
        }

        $managerPermissions = [
            'dashboard',
            'tasks.view',
            'tasks.create',
            'users.view',
            'users.create',
            'users.edit',
            'users.approve',
            'roles.view', // Can view roles but not edit/create
        ];

        $developerPermissions = [
            'dashboard',
            'tasks.view',
            'tasks.create', // Developers can create tasks for themselves or report bugs
        ];

        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => $allPermissions],
            ['name' => 'Manager', 'slug' => 'manager', 'permissions' => $managerPermissions],
            ['name' => 'Developer', 'slug' => 'developer', 'permissions' => $developerPermissions],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'permissions' => $role['permissions']
                ]
            );
        }
    }
}
