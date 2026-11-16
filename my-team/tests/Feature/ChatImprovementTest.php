<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamChat;
use App\Services\TeamMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatImprovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_is_provisioned_backfilled_and_renamed_without_losing_history(): void
    {
        $coach = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id, 'name' => 'difea']);
        $chat = Conversation::where('team_id', $team->id)->where('type', 'team_group')->firstOrFail();
        $chat->messages()->create(['sender_id' => $coach->id, 'message' => 'History']);
        app(TeamChat::class)->ensureGroup($team);
        $team->update(['name' => 'New name']);
        $this->assertSame(1, Conversation::where('team_id', $team->id)->where('type', 'team_group')->count());
        $this->assertSame('New name', $chat->fresh()->subject);
        $this->assertDatabaseHas('messages', ['conversation_id' => $chat->id, 'message' => 'History']);
    }

    public function test_membership_and_private_chat_authorization(): void
    {
        $coach = User::factory()->create(['name' => 'Coach Alice']);
        $player = User::factory()->create(['name' => 'Player Bob']);
        $outsider = User::factory()->create();
        $team = Team::factory()->create(['coach_id' => $coach->id]);
        $chat = Conversation::where('team_id', $team->id)->firstOrFail();
        $this->withSession(['user_id' => $player->id])->get(route('chat.show', $chat))->assertForbidden();
        app(TeamMembership::class)->addPlayer($player, $team);
        $chat->messages()->create(['sender_id' => $coach->id, 'message' => '<script>']);
        $this->withSession(['user_id' => $player->id])->get(route('chat.show', $chat))->assertOk()->assertSee('Coach Alice')->assertSee('&lt;script&gt;', false);
        $payload = ['type' => 'coach_player_private', 'team_id' => $team->id, 'player_id' => $player->id];
        $first = $this->withSession(['user_id' => $player->id])->post(route('chat.create'), $payload);
        $second = $this->withSession(['user_id' => $coach->id])->post(route('chat.create'), $payload);
        $this->assertSame($first->headers->get('Location'), $second->headers->get('Location'));
        $this->assertSame(1, Conversation::where('type', 'coach_player_private')->count());
        $this->withSession(['user_id' => $outsider->id])->post(route('chat.create'), $payload)->assertForbidden();
        $this->withSession(['user_id' => $coach->id])->post(route('chat.create'), array_merge($payload, ['type' => 'team_group']))->assertSessionHasErrors('type');
        app(TeamMembership::class)->leave($player, $team);
        $this->withSession(['user_id' => $player->id])->get(route('chat.poll', $chat).'?after=0')->assertForbidden();
        $this->withSession(['user_id' => $player->id])->postJson(route('chat.messages'), ['conversation_id' => $chat->id, 'message' => 'No'])->assertForbidden();
    }
}
