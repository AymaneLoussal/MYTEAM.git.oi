<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, \Illuminate\Contracts\Eloquent\Validation\ValidationRule|array<mixed>|string>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'coach_id' => User::factory(),
        ];
    }
}
