<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'color' => 'warning',
                'order' => 1,
                'is_default' => true,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in_progress',
                'color' => 'primary',
                'order' => 2,
                'is_default' => false,
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'color' => 'success',
                'order' => 3,
                'is_default' => false,
            ],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
