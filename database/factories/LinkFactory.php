<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'slug' => $this->faker->unique()->lexify('????-????'), // Generate shorter slugs
            'target_url' => $this->faker->url(),
            'is_active' => true,
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('+1 day', '+1 year'),
            'clicks_count' => $this->faker->numberBetween(0, 500)
        ];
    }
}
