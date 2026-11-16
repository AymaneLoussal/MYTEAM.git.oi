<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_create_formation()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);

        $player = User::factory()->create();
        $team->players()->attach($player);
        session(['user_id' => $coach->id]);

        $response = $this->post(route('formations.store', $team), [
            'title' => '4-4-2 Classic',
            'positions' => [['player_id' => $player->id, 'x' => 50, 'y' => 50, 'role' => 'MID']],
        ]);

        $this->assertDatabaseHas('formations', [
            'team_id' => $team->id,
            'title' => '4-4-2 Classic',
        ]);

        $response->assertRedirect();
    }

    public function test_player_cannot_create_formation()
    {
        $coach = User::factory()->create();
        $player = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $team->players()->attach($player);

        session(['user_id' => $player->id]);

        $response = $this->post(route('formations.store', $team), [
            'name' => 'Illegal Formation',
        ]);

        $response->assertStatus(403);
    }
}
