<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_create_team_if_already_coaching()
    {
        $user = User::factory()->create();
        Team::factory()->create(['coach_id' => $user->id]);

        session(['user_id' => $user->id]);

        $response = $this->post(route('dashboard.teams.create'), [
            'name' => 'Second Team',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_player_can_leave_team()
    {
        $coach = User::factory()->create();
        $player = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $team->players()->attach($player);

        session(['user_id' => $player->id]);

        $response = $this->post(route('teams.leave', $team));

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $player->id,
        ]);
    }
}
