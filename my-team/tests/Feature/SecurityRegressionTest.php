<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Formation;
use App\Models\GameMatch;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function team(): array
    {
        $coach = User::factory()->create();
        $player = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $team->players()->attach($player);

        return [$coach, $player, $team];
    }

    private function invitation(Team $team, User $user): Invitation
    {
        return Invitation::factory()->create(['team_id' => $team->id, 'invited_by' => $team->coach_id, 'email' => $user->email]);
    }

    public function test_outsiders_cannot_create_any_chat_type_or_write_team_resources(): void
    {
        [$coach, $player, $team] = $this->team();
        $outsider = User::factory()->create();
        $this->withSession(['user_id' => $outsider->id]);
        foreach (['team_group', 'team_announcement', 'coach_player_private'] as $type) {
            $response = $this->postJson('/chat/conversations', ['team_id' => $team->id, 'type' => $type, 'player_id' => $player->id]);
            $type === 'coach_player_private' ? $response->assertForbidden() : $response->assertUnprocessable();
        }
        foreach (['matches', 'trainings', 'formations', 'invite'] as $action) {
            $this->postJson('/teams/'.$team->id.'/'.$action, [])->assertForbidden();
        }
        $this->patchJson('/teams/'.$team->id.'/players/'.$player->id.'/notes', ['notes' => 'Attack'])->assertForbidden();
        $this->postJson('/teams/'.$team->id.'/join')->assertForbidden();
        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('team_user', 1);
    }

    public function test_players_can_chat_only_with_their_coach_and_not_other_players(): void
    {
        [$coach, $player, $team] = $this->team();
        $other = User::factory()->create();
        $team->players()->attach($other);
        $this->withSession(['user_id' => $player->id]);
        $this->post('/chat/conversations', ['team_id' => $team->id, 'type' => 'coach_player_private', 'player_id' => $other->id])->assertForbidden();
        $this->post('/chat/conversations', ['team_id' => $team->id, 'type' => 'coach_player_private', 'player_id' => $player->id])->assertRedirect();
        $chat = Conversation::where('type', 'coach_player_private')->firstOrFail();
        $this->assertEqualsCanonicalizing([$coach->id, $player->id], $chat->participants()->pluck('users.id')->all());
        $this->withSession(['user_id' => $other->id])->getJson('/chat/'.$chat->id)->assertForbidden();
        $this->postJson('/chat/messages', ['conversation_id' => $chat->id, 'message' => 'No'])->assertForbidden();
    }

    public function test_invalid_chat_creation_is_atomic_and_announcements_are_coach_only(): void
    {
        [$coach, $player, $team] = $this->team();
        $this->withSession(['user_id' => $coach->id]);
        $this->postJson('/chat/conversations', ['team_id' => $team->id, 'type' => 'invalid'])->assertUnprocessable();
        $this->postJson('/chat/conversations', ['team_id' => $team->id, 'type' => 'coach_player_private', 'player_id' => 99999])->assertForbidden();
        $this->assertDatabaseCount('conversations', 1);
        $this->postJson('/chat/conversations', ['team_id' => $team->id, 'type' => 'team_announcement'])->assertUnprocessable();
        $chat = Conversation::create(['team_id' => $team->id, 'type' => 'team_announcement', 'created_by' => $coach->id]);
        $chat->participants()->attach([$coach->id, $player->id]);
        $this->postJson('/chat/messages', ['conversation_id' => $chat->id, 'message' => 'Announcement'])->assertCreated();
        $this->withSession(['user_id' => $player->id])->postJson('/chat/messages', ['conversation_id' => $chat->id, 'message' => 'No'])->assertForbidden();
        $this->get('/chat/'.$chat->id)->assertOk();
    }

    public function test_leaving_revokes_read_send_poll_and_list_access(): void
    {
        [$coach, $player, $team] = $this->team();
        $this->withSession(['user_id' => $coach->id])->post('/chat/conversations', ['team_id' => $team->id, 'type' => 'team_group', 'subject' => 'Restricted conversation']);
        $chat = Conversation::firstOrFail();
        $this->withSession(['user_id' => $player->id])->post('/teams/'.$team->id.'/leave')->assertRedirect();
        $this->get('/chat/'.$chat->id)->assertForbidden();
        $this->getJson('/chat/'.$chat->id.'/messages?after=0')->assertForbidden();
        $this->postJson('/chat/messages', ['conversation_id' => $chat->id, 'message' => 'No'])->assertForbidden();
        $this->get('/chat')->assertDontSee('Restricted conversation');
        // A stale participant row must not grant access either.
        $chat->participants()->attach($player);
        $this->get('/chat/'.$chat->id)->assertForbidden();
    }

    public function test_invitation_join_adds_existing_group_membership(): void
    {
        [$coach, $player, $team] = $this->team();
        $this->withSession(['user_id' => $coach->id])->post('/chat/conversations', ['team_id' => $team->id, 'type' => 'team_group']);
        $newPlayer = User::factory()->create();
        $invite = $this->invitation($team, $newPlayer);
        $this->withSession(['user_id' => $newPlayer->id])->post('/teams/'.$team->id.'/join', ['token' => $invite->token])->assertRedirect('/teams/'.$team->id);
        $this->assertSame('accepted', $invite->fresh()->status);
        $this->get('/chat/'.Conversation::firstOrFail()->id)->assertOk();
    }

    public function test_membership_rules_apply_to_all_entry_points(): void
    {
        [$coach, $player, $team] = $this->team();
        $this->withSession(['user_id' => $player->id]);
        $this->postJson('/teams', ['name' => 'Second'])->assertUnprocessable();
        $this->post('/dashboard/teams', ['name' => 'Second'])->assertSessionHasErrors('name');
        $other = Team::factory()->create();
        $invite = $this->invitation($other, $player);
        $this->postJson('/invitations/'.$invite->token.'/accept')->assertUnprocessable();
        $this->assertDatabaseMissing('team_user', ['team_id' => $other->id, 'user_id' => $player->id]);
        $this->assertSame('pending', $invite->fresh()->status);
    }

    public function test_notes_are_scoped_to_roster_and_not_exposed_to_players(): void
    {
        [$coach, $player, $team] = $this->team();
        $other = Team::factory()->create();
        $other->players()->attach($player, ['notes' => 'Other team secret']);
        $outsider = User::factory()->create();
        $this->withSession(['user_id' => $coach->id]);
        $this->patch('/teams/'.$team->id.'/players/'.$player->id.'/notes', ['notes' => 'Our secret'])->assertRedirect();
        $this->patch('/teams/'.$team->id.'/players/'.$outsider->id.'/notes', ['notes' => 'No'])->assertNotFound();
        $this->assertDatabaseHas('team_user', ['team_id' => $other->id, 'user_id' => $player->id, 'notes' => 'Other team secret']);
        $this->get('/teams/'.$team->id)->assertSee('Our secret')->assertDontSee('Other team secret');
        $this->withSession(['user_id' => $player->id])->get('/teams/'.$team->id)->assertDontSee('Our secret');
        $this->assertNull($player->fresh()->notes);
    }

    public function test_other_coaches_and_players_cannot_modify_resources(): void
    {
        [$coach, $player, $team] = $this->team();
        $outsider = User::factory()->create();
        $match = GameMatch::create(['team_id' => $team->id, 'created_by' => $coach->id, 'starts_at' => now()->addDay()]);
        $training = Training::create(['team_id' => $team->id, 'created_by' => $coach->id, 'title' => 'Training', 'starts_at' => now()->addDay()]);
        $formation = Formation::create(['team_id' => $team->id, 'created_by' => $coach->id, 'title' => 'Formation', 'positions' => []]);
        foreach ([$player, $outsider] as $user) {
            $this->withSession(['user_id' => $user->id]);
            foreach (['matches/'.$match->id, 'trainings/'.$training->id, 'formations/'.$formation->id] as $path) {
                $this->get('/'.$path.'/edit')->assertForbidden();
                $this->patchJson('/'.$path, [])->assertForbidden();
                $this->deleteJson('/'.$path)->assertForbidden();
            }
            $this->patch('/matches/'.$match->id.'/lineup', ['roles' => [$player->id => 'starter']])->assertForbidden();
        }
    }

    public function test_login_throttles_and_recovers_after_window(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertUnauthorized();
        }
        $this->postJson('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->postJson('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertUnauthorized();
    }

    public function test_logout_clears_session_and_rotates_csrf_token(): void
    {
        $user = User::factory()->create();
        $this->withSession(['user_id' => $user->id, 'invitation_token' => 'old', '_token' => 'old-token']);
        $this->post('/logout')->assertRedirect('/')->assertSessionMissing('user_id')->assertSessionMissing('invitation_token');
        $this->assertNotSame('old-token', session()->token());
        $this->getJson('/teams')->assertUnauthorized();
    }

    public function test_claiming_an_email_does_not_reveal_or_bypass_invitation_token(): void
    {
        [$coach, $player, $team] = $this->team();
        $claimant = User::factory()->create(['email_verified_at' => null]);
        $invite = $this->invitation($team, $claimant);
        $this->withSession(['user_id' => $claimant->id]);
        $this->get('/dashboard')->assertOk()->assertDontSee($invite->token);
        $this->getJson('/invitations')->assertOk()->assertJsonMissing(['token' => $invite->token]);
        $this->postJson('/teams/'.$team->id.'/join')->assertUnprocessable();
        $this->postJson('/teams/'.$team->id.'/join', ['token' => str_repeat('z', 64)])->assertNotFound();
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $claimant->id]);
    }

    public function test_incremental_chat_is_bounded_and_keeps_message_text_literal(): void
    {
        [$coach, $player, $team] = $this->team();
        $this->withSession(['user_id' => $coach->id])->post('/chat/conversations', ['team_id' => $team->id, 'type' => 'team_group']);
        $chat = Conversation::firstOrFail();
        for ($i = 0; $i < 105; $i++) {
            $chat->messages()->create(['sender_id' => $player->id, 'message' => '<img src=x onerror=alert(1)>']);
        }
        $this->get('/chat/'.$chat->id)->assertOk()->assertSee('&lt;img', false)->assertDontSee('<img src=x', false);
        $this->getJson('/chat/'.$chat->id.'/messages?after=0')->assertOk()->assertJsonCount(100);
        $this->getJson('/chat/'.$chat->id.'/messages?after=100')->assertOk()->assertJsonCount(5);
        $this->getJson('/chat/'.$chat->id.'/messages?after=-1')->assertUnprocessable();
    }

    public function test_registration_and_invitation_send_are_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/register', [])->assertUnprocessable();
        }
        $this->postJson('/register', [])->assertStatus(429);
        [$coach, $player, $team] = $this->team();
        $this->withSession(['user_id' => $coach->id]);
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/teams/'.$team->id.'/invite', [])->assertUnprocessable();
        }
        $this->postJson('/dashboard/teams/'.$team->id.'/invite',[])->assertStatus(429);
    }
}
