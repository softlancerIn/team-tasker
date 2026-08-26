<?php

namespace Database\Seeders;

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
            'dashboard.view',
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'tasks.my_tasks',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tickets.view',
            'tickets.reply',
            'tickets.assign',
            'clients.view',
            'clients.create',
            'clients.edit',
            'chat.view',
            'users.view',
            'users.create',
            'users.edit',
            'users.approve',
            'roles.view',
            'meetings.view',
            'meetings.create',
            'meetings.join',
            'meetings.cancel',
        ];

        $clientPermissions = [
            'dashboard.view',
            'projects.view',
            'tasks.my_tasks',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'chat.view',
        ];

        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'permissions' => $allPermissions],
            ['name' => 'Manager', 'slug' => 'manager', 'permissions' => $managerPermissions],
            ['name' => 'Client', 'slug' => 'client', 'permissions' => $clientPermissions],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'permissions' => $role['permissions'],
                ]
            );
        }
    }
}
