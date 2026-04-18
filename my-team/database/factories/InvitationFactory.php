<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, \Illuminate\Contracts\Eloquent\Validation\ValidationRule|array<mixed>|string>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'invited_by' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ];
    }
}
