<?php
namespace Tests\Feature;
use App\Models\{Invitation,Team,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Mail,Cache};
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;
class InvitationResendTest extends TestCase {
 use RefreshDatabase;
 public function test_failure_preserves_invitation_and_resend_reuses_token(): void {
  config(['mail.default'=>'smtp']);$coach=User::factory()->create();$team=Team::factory()->create(['coach_id'=>$coach->id]);
  Mail::shouldReceive('to')->once()->andThrow(new TransportException('SECRET must not appear'));
  $this->withSession(['user_id'=>$coach->id])->postJson(route('teams.invite',$team),['email'=>'test@example.test'])->assertUnprocessable()->assertDontSee('SECRET');
  $invite=Invitation::firstOrFail();$token=$invite->token;$expires=$invite->expires_at->toISOString();
  Mail::shouldReceive('getDefaultDriver')->andReturn('smtp');Mail::fake();$this->postJson(route('invitations.resend',$invite))->assertUnprocessable();
  $this->travel(61)->seconds();$this->postJson(route('invitations.resend',$invite))->assertCreated();
  Mail::assertSent(\App\Mail\TeamInvitationMail::class,1);
  $this->assertSame($token,$invite->fresh()->token);$this->assertSame($expires,$invite->fresh()->expires_at->toISOString());
  $this->postJson(route('teams.invite',$team),['email'=>$invite->email])->assertUnprocessable();$this->assertDatabaseCount('invitations',1);
 }
 public function test_resend_requires_coach_and_active_invitation(): void {
  Mail::fake();$coach=User::factory()->create();$team=Team::factory()->create(['coach_id'=>$coach->id]);$invite=Invitation::factory()->create(['team_id'=>$team->id,'invited_by'=>$coach->id]);
  $this->withSession(['user_id'=>User::factory()->create()->id])->postJson(route('invitations.resend',$invite))->assertForbidden();
  $invite->update(['expires_at'=>now()->subDay()]);$this->withSession(['user_id'=>$coach->id])->postJson(route('invitations.resend',$invite))->assertStatus(410);Mail::assertNothingSent();
 }
}
