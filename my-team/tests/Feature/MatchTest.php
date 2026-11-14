<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_create_match()
    {
        $coach = User::factory()->create();
        $session = session(['user_id' => $coach->id]);
        $team = Team::factory()->create(['coach_id' => $coach->id]);

        $response = $this->post(route('matches.store', $team), [
            'starts_at' => now()->addDay()->format('Y-m-d\\TH:i'),
            'location' => 'Stadium',
            'opponent' => 'Rival FC',
        ]);

        $response->assertRedirect(route('matches.lineup', Team::first()->matches()->first()));
        $this->assertDatabaseHas('matches', [
            'team_id' => $team->id,
            'created_by' => $coach->id,
        ]);
    }

    public function test_coach_can_set_lineup()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $player = User::factory()->create();
        $team->players()->attach($player);
        $match = GameMatch::create(['team_id' => $team->id, 'created_by' => $coach->id, 'starts_at' => now()->addDay()]);
        session(['user_id' => $coach->id]);

        $response = $this->patch(route('matches.lineup.update', $match), [
            'roles' => [
                $player->id => 'starter',
            ],
        ]);

        $response->assertRedirect(route('matches.show', $match));
        $this->assertDatabaseHas('match_user', [
            'match_id' => $match->id,
            'user_id' => $player->id,
            'role' => 'starter',
        ]);
    }

    public function test_non_coach_cannot_create_match()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $player = User::factory()->create();
        session(['user_id' => $player->id]);

        $response = $this->post(route('matches.store', $team), [
            'starts_at' => now()->addDay()->format('Y-m-d\\TH:i'),
        ]);

        $response->assertStatus(403);
    }
}
