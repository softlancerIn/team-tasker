<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'resolved', 'closed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'email_source' => $this->faker->optional()->safeEmail(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
