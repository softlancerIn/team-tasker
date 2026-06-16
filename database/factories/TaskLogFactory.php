<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskLog>
 */
class TaskLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::inRandomOrder()->first()->id ?? Task::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'note' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['log', 'message']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
