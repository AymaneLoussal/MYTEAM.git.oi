<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_players_can_discover_their_team_resources_and_active_section(): void
    {
        $coach = User::factory()->create();
        $player = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $team->players()->attach($player);
        $this->withSession(['user_id' => $player->id]);

        $this->get(route('teams.show', $team))->assertOk()
            ->assertSee(route('matches.team', $team))
            ->assertSee(route('trainings.team', $team))
            ->assertSee(route('formations.team', $team))
            ->assertDontSee('Schedule Training');

        $html = $this->get(route('matches.team', $team))->assertOk()->getContent();
        preg_match_all('/<a[^>]*aria-current="page"[^>]*>(.*?)<\/a>/s', $html, $active);
        $this->assertSame(['Matches'], array_map('trim', $active[1]));
    }

    public function test_chat_creation_errors_and_empty_state_are_visible(): void
    {
        $user = User::factory()->create();
        Team::factory()->create(['coach_id' => $user->id]);
        $this->withSession(['user_id' => $user->id]);
        $this->from(route('chat.index'))->post(route('chat.create'), [])
            ->assertRedirect(route('chat.index'))->assertSessionHasErrors();
        $this->view('components.feedback', ['errors' => session('errors')])
            ->assertSee('role="alert"', false);
        $this->get(route('chat.index'))->assertOk()
            ->assertSee('No private conversations yet')
            ->assertSee('<dialog id="chat-modal"', false);
    }
}
