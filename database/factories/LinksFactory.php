<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Links>
 */
class LinksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),                         // أو رقم ثابت لو عايز
            'slug'         => $this->faker->boolean(20)
                ? null
                : Str::slug($this->faker->unique()->words(2, true)), // nullable|unique
            'target_url'   => $this->faker->url(),
            'is_active'    => $this->faker->boolean(90),
            'expires_at'   => $this->faker->optional(0.3)->dateTimeBetween('now', '+30 days'),
            'clicks_count' => $this->faker->numberBetween(0, 500)
        ];
    }
}
