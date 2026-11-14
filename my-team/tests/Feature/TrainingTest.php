<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_create_training()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        session(['user_id' => $coach->id]);

        $response = $this->post(route('trainings.store', $team), [
            'title' => 'Aerobic Fitness',
            'starts_at' => now()->addDay()->format('Y-m-d\\TH:i'),
            'location' => 'Field 1',
        ]);

        $response->assertRedirect(route('trainings.team', $team));
        $this->assertDatabaseHas('trainings', [
            'team_id' => $team->id,
            'created_by' => $coach->id,
        ]);
    }

    public function test_player_can_view_team_trainings()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $player = User::factory()->create();
        $team->players()->attach($player);
        Training::create(['team_id' => $team->id, 'created_by' => $coach->id, 'title' => 'Training', 'starts_at' => now()->addDay()]);
        session(['user_id' => $player->id]);

        $response = $this->get(route('trainings.team', $team));

        $response->assertStatus(200);
    }
}
