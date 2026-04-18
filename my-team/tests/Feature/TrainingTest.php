<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function coach_can_create_training()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        session(['user_id' => $coach->id]);

        $response = $this->post(route('trainings.store', $team), [
            'title' => 'Aerobic Fitness',
            'starts_at' => now()->addDays(1),
            'location' => 'Field 1',
        ]);

        $response->assertRedirect(route('trainings.team', $team));
        $this->assertDatabaseHas('trainings', [
            'team_id' => $team->id,
            'created_by' => $coach->id,
        ]);
    }

    /** @test */
    public function player_can_view_team_trainings()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $player = User::factory()->create();
        $team->players()->attach($player);
        \App\Models\Training::factory()->create(['team_id' => $team->id]);
        session(['user_id' => $player->id]);

        $response = $this->get(route('trainings.team', $team));

        $response->assertStatus(200);
    }
}

