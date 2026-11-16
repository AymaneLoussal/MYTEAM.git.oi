<?php
namespace Tests\Feature;

use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_mailer_refuses_to_log_invitation_tokens_or_create_an_invite(): void
    {
        config(['mail.default' => 'log']);
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $this->withSession(['user_id' => $coach->id])->post(route('teams.invite', $team), ['email' => 'player@example.test'])
            ->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('invitations', ['team_id' => $team->id]);
    }

    public function test_invitation_is_sent_to_mail_transport_and_dashboard_does_not_expose_token(): void
    {
        Mail::fake();
        $coach = User::factory()->create();
        $player = User::factory()->create(['email' => 'player@example.test', 'email_verified_at' => null]);
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $this->withSession(['user_id' => $coach->id])->post(route('teams.invite', $team), ['email' => $player->email])->assertRedirect();
        $invitation = Invitation::firstOrFail();
        Mail::assertSent(TeamInvitationMail::class, fn ($mail) => $mail->hasTo($player->email));
        $this->withSession(['user_id' => $player->id])->get(route('dashboard'))->assertOk()
            ->assertSee($team->name)->assertSee('Review Invitation')->assertDontSee($invitation->token);
        $this->getJson(route('invitations.index'))->assertDontSee($invitation->token);
    }

    public function test_token_allows_unverified_invited_account_to_decline_but_wrong_account_cannot(): void
    {
        $coach = User::factory()->create();
        $player = User::factory()->create(['email_verified_at' => null]);
        $other = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $invite = Invitation::factory()->create(['team_id' => $team->id, 'invited_by' => $coach->id, 'email' => $player->email]);
        $this->withSession(['user_id' => $other->id])->post(route('invitations.reject', $invite->token))->assertForbidden();
        $this->withSession(['user_id' => $player->id])->post(route('invitations.reject', $invite->token))->assertRedirect();
        $this->assertSame('rejected', $invite->fresh()->status);
        $this->post(route('invitations.reject', $invite->token))->assertStatus(410);
    }
}
