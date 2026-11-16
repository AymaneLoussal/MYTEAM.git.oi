<?php

namespace Tests\Audit;

use App\Models\{User, Team, Invitation, Formation, GameMatch, Training, Conversation};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Audit-only regression probes; run with audit/run.ps1 against in-memory SQLite. */
class ProjectAuditTest extends TestCase
{
    use RefreshDatabase;

    private function coachTeam(): array
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $this->withSession(['user_id' => $coach->id]);
        return [$coach, $team];
    }

    public function test_authenticated_pages_render(): void
    {
        [$coach, $team] = $this->coachTeam();
        foreach (['/dashboard', '/teams', '/teams/'.$team->id, '/profile', '/trainings', '/matches', '/formations', '/chat'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_registration_login_logout(): void
    {
        $this->post('/register', ['name'=>'Audit User','email'=>'audit@example.test','password'=>'Audit-password-123','password_confirmation'=>'Audit-password-123'])->assertRedirect('/login');
        $this->post('/login', ['email'=>'audit@example.test','password'=>'Audit-password-123'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_team_creation_endpoint(): void
    {
        $user = User::factory()->create();
        $this->withSession(['user_id'=>$user->id])->postJson('/teams', ['name'=>'Audit Team'])->assertCreated();
    }

    public function test_profile_name_update(): void
    {
        $this->coachTeam();
        $this->patchJson('/profile', ['name'=>'Updated'])->assertOk();
    }

    public function test_profile_blank_password_preserves_hash(): void
    {
        [$coach] = $this->coachTeam();
        $hash = $coach->password;
        $this->patch('/profile', ['name'=>'Updated','password'=>'','password_confirmation'=>''])->assertRedirect('/profile');
        $this->assertSame($hash, $coach->fresh()->password);
    }

    public function test_new_invitee_can_open_registration(): void
    {
        [$coach, $team] = $this->coachTeam();
        $invite = Invitation::create(['team_id'=>$team->id,'email'=>'new@example.test','invited_by'=>$coach->id,'token'=>str_repeat('a',64),'status'=>'pending','expires_at'=>now()->addDay()]);
        $this->post('/invitations/'.$invite->token.'/accept')->assertOk();
    }

    public function test_inline_invitation_registration_works(): void
    {
        [$coach, $team] = $this->coachTeam();
        $invite = Invitation::create(['team_id'=>$team->id,'email'=>'inline@example.test','invited_by'=>$coach->id,'token'=>str_repeat('c',64),'status'=>'pending','expires_at'=>now()->addDay()]);
        $this->withSession(['user_id'=>null])->post('/invitations/'.$invite->token.'/register-and-accept',['name'=>'New Player','password'=>'Audit-password-123','password_confirmation'=>'Audit-password-123'])->assertRedirect('/teams/'.$team->id);
        $this->assertSame('accepted',$invite->fresh()->status);
    }

    public function test_accepted_invitation_displays_correct_status(): void
    {
        [$coach, $team] = $this->coachTeam();
        $invite = Invitation::create(['team_id'=>$team->id,'email'=>'accepted@example.test','invited_by'=>$coach->id,'token'=>str_repeat('d',64),'status'=>'accepted','expires_at'=>now()->addDay()]);
        $this->get('/invitations/'.$invite->token)->assertOk()->assertSee('Already Accepted');
    }

    public function test_formation_show_page(): void
    {
        [$coach, $team] = $this->coachTeam();
        $formation = Formation::create(['team_id'=>$team->id,'created_by'=>$coach->id,'title'=>'Audit','positions'=>[]]);
        $this->get('/formations/'.$formation->id)->assertOk();
    }

    public function test_admin_seed_grants_admin_access(): void
    {
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $admin = User::where('email','admin@team.com')->firstOrFail();
        $this->withSession(['user_id'=>$admin->id])->get('/admin/users')->assertOk();
        $ordinary = User::factory()->create();
        $this->withSession(['user_id'=>$ordinary->id])->get('/admin/users')->assertForbidden();
    }

    public function test_existing_invitee_can_accept(): void
    {
        [$coach, $team] = $this->coachTeam();
        $player = User::factory()->create();
        $invite = Invitation::create(['team_id'=>$team->id,'email'=>$player->email,'invited_by'=>$coach->id,'token'=>str_repeat('b',64),'status'=>'pending','expires_at'=>now()->addDay()]);
        $this->withSession(['user_id'=>$player->id])->post('/invitations/'.$invite->token.'/accept')->assertRedirect('/teams/'.$team->id);
        $this->assertDatabaseHas('team_user',['team_id'=>$team->id,'user_id'=>$player->id]);
    }

    public function test_formation_create_page(): void
    {
        [, $team] = $this->coachTeam();
        $this->get('/teams/'.$team->id.'/formations/create')->assertOk();
    }

    public function test_formation_team_page(): void
    {
        [, $team] = $this->coachTeam();
        $this->get('/teams/'.$team->id.'/formations')->assertOk();
    }

    public function test_formation_browser_form_payload(): void
    {
        [$coach, $team] = $this->coachTeam();
        $player = User::factory()->create();
        $team->players()->attach($player);
        $this->post('/teams/'.$team->id.'/formations',['title'=>'Audit Formation','positions'=>json_encode([['player_id'=>$player->id,'x'=>50,'y'=>50,'role'=>'starter']])])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('formations',1);
    }

    public function test_formation_rejects_non_team_player(): void
    {
        [, $team] = $this->coachTeam();
        $outsider = User::factory()->create();
        $this->postJson('/teams/'.$team->id.'/formations',['title'=>'Invalid Roster','positions'=>[['player_id'=>$outsider->id,'x'=>50,'y'=>50,'role'=>'GK']]])->assertUnprocessable();
    }

    public function test_outsider_cannot_create_private_team_chat(): void
    {
        [, $team] = $this->coachTeam();
        $player = User::factory()->create();
        $outsider = User::factory()->create();
        $team->players()->attach($player);
        $this->withSession(['user_id'=>$outsider->id])->post('/chat/conversations',['team_id'=>$team->id,'type'=>'coach_player_private','player_id'=>$player->id])->assertForbidden();
    }

    public function test_leaving_team_revokes_chat_access(): void
    {
        [$coach, $team] = $this->coachTeam();
        $player = User::factory()->create();
        $team->players()->attach($player);
        $chat = app(\App\Services\TeamChat::class)->ensureGroup($team);
        $this->withSession(['user_id'=>$player->id])->post('/teams/'.$team->id.'/leave')->assertRedirect('/teams');
        $this->get('/chat/'.$chat->id)->assertForbidden();
    }

    public function test_match_creation_show_and_lineup(): void
    {
        [$coach, $team] = $this->coachTeam();
        $player = User::factory()->create();
        $team->players()->attach($player);
        $this->postJson('/teams/'.$team->id.'/matches',['starts_at'=>now()->addDay()->format('Y-m-d\TH:i'),'opponent'=>'Opponent'])->assertCreated();
        $match = GameMatch::firstOrFail();
        $this->get('/matches/'.$match->id)->assertOk();
        $this->get('/matches/'.$match->id.'/lineup')->assertOk();
        $this->patch('/matches/'.$match->id.'/lineup',['roles'=>[$player->id=>'starter']])->assertRedirect('/matches/'.$match->id);
        $this->assertDatabaseHas('match_user',['match_id'=>$match->id,'user_id'=>$player->id,'role'=>'starter']);
    }

    public function test_training_create_and_read(): void
    {
        [, $team] = $this->coachTeam();
        $this->postJson('/teams/'.$team->id.'/trainings',['title'=>'Audit Training','starts_at'=>now()->addDay()->format('Y-m-d\TH:i')])->assertCreated();
        $training = Training::firstOrFail();
        $this->get('/trainings/'.$training->id)->assertOk();
        $this->get('/trainings/'.$training->id.'/edit')->assertOk();
    }

    public function test_outsider_cannot_read_team_match_or_training(): void
    {
        [$coach, $team] = $this->coachTeam();
        $match = GameMatch::create(['team_id'=>$team->id,'created_by'=>$coach->id,'starts_at'=>now()->addDay()]);
        $training = Training::create(['team_id'=>$team->id,'created_by'=>$coach->id,'title'=>'Private','starts_at'=>now()->addDay()]);
        $outsider = User::factory()->create();
        $this->withSession(['user_id'=>$outsider->id]);
        foreach (['/teams/'.$team->id,'/matches/'.$match->id,'/trainings/'.$training->id] as $path) {
            $this->get($path)->assertForbidden();
        }
    }
}
