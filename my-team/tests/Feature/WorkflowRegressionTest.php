<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\GameMatch;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function formationContext(): array
    {
        $coach = User::factory()->create();
        $player = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $team->players()->attach($player);
        $this->withSession(['user_id' => $coach->id]);

        return [$team, ['title' => 'Formation', 'positions' => [['player_id' => $player->id, 'x' => 40, 'y' => 60, 'role' => 'MID']]]];
    }

    public function test_formation_browser_lifecycle_and_roster_rendering(): void
    {
        [$team,$payload] = $this->formationContext();
        $this->get('/teams/'.$team->id.'/formations/create')->assertOk();
        $this->post('/teams/'.$team->id.'/formations', $payload)->assertRedirect('/teams/'.$team->id.'/formations');
        $formation = Formation::firstOrFail();
        $this->get('/formations/'.$formation->id)->assertOk();
        $this->get('/formations/'.$formation->id.'/edit')->assertOk()->assertSee('value="Formation"', false);
        $payload['title'] = 'Changed';
        $payload['positions'][0]['x'] = 25;
        $this->patch('/formations/'.$formation->id, $payload)->assertRedirect('/formations/'.$formation->id);
        $this->assertSame(25, $formation->fresh()->positions[0]['x']);
        $this->assertSame(1, Formation::count());
        $this->delete('/formations/'.$formation->id)->assertRedirect('/teams/'.$team->id.'/formations');
        $this->assertDatabaseCount('formations', 0);
    }

    public function test_invalid_formation_payloads_are_rejected_without_writes(): void
    {
        [$team,$payload] = $this->formationContext();
        $invalid = ['{broken', [], [['x' => 10]], array_merge($payload['positions'], $payload['positions'])];
        foreach ($invalid as $positions) {
            $this->postJson('/teams/'.$team->id.'/formations', ['title' => 'Invalid', 'positions' => $positions])->assertUnprocessable();
        }
        $payload['positions'][0]['x'] = 101;
        $this->postJson('/teams/'.$team->id.'/formations', $payload)->assertUnprocessable();
        $payload['positions'][0]['x'] = 50;
        $payload['positions'][0]['role'] = 'invalid';
        $this->postJson('/teams/'.$team->id.'/formations', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('formations', 0);
    }

    public function test_formation_update_rejects_another_teams_player(): void
    {
        [$team,$payload] = $this->formationContext();
        $this->postJson('/teams/'.$team->id.'/formations', $payload)->assertCreated();
        $formation = Formation::firstOrFail();
        $payload['positions'][0]['player_id'] = User::factory()->create()->id;
        $this->patchJson('/formations/'.$formation->id, $payload)->assertUnprocessable();
        $this->assertNotSame($payload['positions'], $formation->fresh()->positions);
    }

    public function test_profile_changes_require_current_password_and_preserve_hash_when_blank(): void
    {
        $user = User::factory()->create();
        $hash = $user->password;
        $this->withSession(['user_id' => $user->id]);
        $this->patchJson('/profile', ['email' => 'changed@example.test'])->assertUnprocessable();
        $this->patchJson('/profile', ['password' => 'new-password', 'password_confirmation' => 'new-password', 'current_password' => 'wrong'])->assertUnprocessable();
        $this->patch('/profile', ['name' => 'Changed', 'password' => ''])->assertRedirect('/profile');
        $this->assertSame($hash, $user->fresh()->password);
        $this->patchJson('/profile', ['email' => 'changed@example.test', 'password' => 'new-password', 'password_confirmation' => 'new-password', 'current_password' => 'password'])->assertOk();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertSame('changed@example.test', $user->fresh()->email);
    }

    public function test_avatar_upload_uses_public_disk_and_returns_accessor(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar' => 'avatars/old.png']);
        Storage::disk('public')->put('avatars/old.png', 'old');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aO1sAAAAASUVORK5CYII=');
        $this->withSession(['user_id' => $user->id])->patchJson('/profile', [
            'avatar' => UploadedFile::fake()->createWithContent('avatar.png', $png),
        ])->assertOk()->assertJsonPath('user.avatar_url', fn ($url) => str_contains($url, '/storage/avatars/'));
        $storedAvatar = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($storedAvatar);
        $this->assertSame('/storage/'.$storedAvatar, parse_url($user->fresh()->avatar_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists(User::findOrFail($user->id)->avatar);
        Storage::disk('public')->assertMissing('avatars/old.png');
        $this->patchJson('/profile', [
            'avatar' => UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;'),
        ])->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->patchJson('/profile', [
            'avatar' => UploadedFile::fake()->create('too-large.jpg', 2049, 'image/jpeg'),
        ])->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame($storedAvatar, $user->fresh()->avatar);
    }

    public function test_expired_and_processed_invitations_cannot_be_reused(): void
    {
        $player = User::factory()->create();
        $this->withSession(['user_id' => $player->id]);
        foreach (['expired', 'accepted', 'rejected'] as $status) {
            $invitation = Invitation::factory()->create(['email' => $player->email, 'status' => $status]);
            $this->post('/invitations/'.$invitation->token.'/accept')->assertStatus(410);
            $this->post('/invitations/'.$invitation->token.'/register-and-accept')->assertStatus(410);
        }
        $expired = Invitation::factory()->create(['email' => $player->email, 'expires_at' => now()->subDay()]);
        $this->post('/invitations/'.$expired->token.'/accept')->assertStatus(410);
        $this->assertDatabaseCount('team_user', 0);
    }

    public function test_wrong_account_cannot_accept_or_decline_an_invitation(): void
    {
        $invitation = Invitation::factory()->create();
        $outsider = User::factory()->create();
        User::factory()->create(['email' => $invitation->email]);
        $this->withSession(['user_id' => $outsider->id]);
        $this->postJson('/invitations/'.$invitation->token.'/accept')->assertForbidden();
        $this->postJson('/invitations/'.$invitation->token.'/reject')->assertForbidden();
        $this->assertSame('pending', $invitation->fresh()->status);
        $this->assertDatabaseCount('team_user', 0);
    }

    public function test_login_resumes_invitation_and_acceptance_is_idempotently_guarded(): void
    {
        $player = User::factory()->create();
        $invitation = Invitation::factory()->create(['email' => $player->email]);
        $this->get('/invitations/'.$invitation->token)->assertOk();
        $this->post('/login', ['email' => $player->email, 'password' => 'password'])->assertRedirect('/invitations/'.$invitation->token);
        $this->post('/invitations/'.$invitation->token.'/accept')->assertRedirect('/teams/'.$invitation->team_id);
        $this->post('/invitations/'.$invitation->token.'/accept')->assertStatus(410);
        $this->assertDatabaseCount('team_user', 1);
    }

    public function test_invitation_registration_does_not_replace_existing_accounts(): void
    {
        $user = User::factory()->create();
        $hash = $user->password;
        $invitation = Invitation::factory()->create(['email' => $user->email]);
        $this->post('/invitations/'.$invitation->token.'/register-and-accept', ['name' => 'Attacker', 'password' => 'replacement', 'password_confirmation' => 'replacement'])->assertRedirect('/login');
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame('pending', $invitation->fresh()->status);
        $this->assertDatabaseCount('team_user', 0);
    }

    public function test_invitation_registration_rotates_session_and_uses_invited_email(): void
    {
        $invitation = Invitation::factory()->create();
        $this->get('/invitations/'.$invitation->token);
        $sessionId = session()->getId();
        $this->post('/invitations/'.$invitation->token.'/register-and-accept', [
            'name' => 'Invited', 'email' => 'different@example.test', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertRedirect('/teams/'.$invitation->team_id);
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertDatabaseHas('users', ['email' => $invitation->email]);
        $this->assertDatabaseMissing('users', ['email' => 'different@example.test']);
    }

    public function test_invitation_states_are_distinct_even_after_expiry_date(): void
    {
        foreach (['accepted' => 'Already Accepted', 'rejected' => 'Invitation Declined', 'pending' => 'Invitation Expired'] as $status => $label) {
            $invitation = Invitation::factory()->create(['status' => $status, 'expires_at' => now()->subDay()]);
            $this->get('/invitations/'.$invitation->token)->assertOk()->assertSee($label);
        }
    }

    public function test_repeated_admin_seeding_preserves_existing_password(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = User::where('email', 'admin@team.com')->firstOrFail();
        $admin->update(['password' => 'changed-password']);
        $hash = $admin->fresh()->password;
        $this->seed(AdminSeeder::class);
        $this->assertSame($hash, $admin->fresh()->password);
        $this->assertSame(1, User::where('email', 'admin@team.com')->count());
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_coach_can_update_and_delete_match_and_training(): void
    {
        [$team] = $this->formationContext();
        $match = GameMatch::create(['team_id' => $team->id, 'created_by' => $team->coach_id, 'starts_at' => now()->addDay()]);
        $training = Training::create(['team_id' => $team->id, 'created_by' => $team->coach_id, 'title' => 'Training', 'starts_at' => now()->addDay()]);
        $this->patchJson('/matches/'.$match->id, ['starts_at' => now()->addDays(2)->toDateTimeString(), 'opponent' => 'Updated'])->assertOk();
        $this->assertSame('Updated', $match->fresh()->opponent);
        $this->patchJson('/trainings/'.$training->id, ['title' => 'Updated', 'starts_at' => now()->addDays(2)->toDateTimeString()])->assertOk();
        $this->assertSame('Updated', $training->fresh()->title);
        $this->deleteJson('/matches/'.$match->id)->assertOk();
        $this->deleteJson('/trainings/'.$training->id)->assertOk();
        $this->assertDatabaseCount('matches',0);
        $this->assertDatabaseCount('trainings',0);
    }
}
