<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChatIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_duplicate_group_and_private_pair(): void
    {
        $coach = User::factory()->create();
        $player = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        try {
            DB::table('conversations')->insert(['team_id' => $team->id, 'type' => 'team_group', 'created_by' => $coach->id]);
            $this->fail('Duplicate group was accepted');
        } catch (QueryException $e) { $this->assertStringContainsString('UNIQUE', $e->getMessage()); }
        DB::table('conversations')->insert(['team_id' => $team->id, 'type' => 'coach_player_private', 'private_player_id' => $player->id, 'created_by' => $coach->id]);
        $this->expectException(QueryException::class);
        DB::table('conversations')->insert(['team_id' => $team->id, 'type' => 'coach_player_private', 'private_player_id' => $player->id, 'created_by' => $coach->id]);
    }

    public function test_reconciliation_removes_only_empty_matching_duplicates_and_reports_conflicts(): void
    {
        DB::statement('DROP INDEX conversations_one_team_group');
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $group = Conversation::where('team_id', $team->id)->firstOrFail();
        $duplicate = Conversation::create(['type' => 'team_group', 'team_id' => $team->id, 'created_by' => $coach->id, 'subject' => $team->name]);
        $duplicate->participants()->attach($coach->id);
        $conflict = Conversation::create(['type' => 'team_group', 'team_id' => $team->id, 'created_by' => $coach->id, 'subject' => $team->name]);
        $conflict->participants()->attach($coach->id);
        $conflict->messages()->create(['sender_id' => $coach->id, 'message' => 'Keep me']);
        $this->artisan('chat:reconcile-groups')->assertExitCode(0);
        $this->assertDatabaseHas('conversations', ['id' => $duplicate->id]);
        $this->artisan('chat:reconcile-groups', ['--apply' => true])->expectsOutputToContain('manual review required')->assertExitCode(0);
        $this->assertDatabaseMissing('conversations', ['id' => $duplicate->id]);
        $this->assertDatabaseHas('messages', ['conversation_id' => $conflict->id, 'message' => 'Keep me']);
        $this->artisan('chat:reconcile-groups', ['--apply' => true])->assertExitCode(0);
        $this->assertDatabaseHas('conversations', ['id' => $group->id]);
    }
}
