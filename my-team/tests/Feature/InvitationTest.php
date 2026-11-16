<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_send_invitation()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);

        session(['user_id' => $coach->id]);

        $response = $this->post(route('dashboard.teams.invite', $team), [
            'email' => 'player@example.com',
        ]);

        $this->assertDatabaseHas('invitations', [
            'email' => 'player@example.com',
            'team_id' => $team->id,
            'invited_by' => $coach->id,
        ]);

        $response->assertRedirect();
    }

    public function test_user_can_accept_invitation()
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $invitation = Invitation::factory()->create([
            'team_id' => $team->id,
            'invited_by' => $coach->id,
            'email' => 'player@example.com',
        ]);

        // Simulate accepting invitation (assuming it's a public route)
        $response = $this->post(route('invitations.register', $invitation->token), [
            'name' => 'New Player',
            'email' => 'player@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'player@example.com']);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => User::where('email', 'player@example.com')->first()->id,
        ]);
    }
}
