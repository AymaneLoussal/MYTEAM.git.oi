<?php
namespace Tests\Feature;
use App\Models\{Team,User,Formation};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class FormationSquadTest extends TestCase
{
    use RefreshDatabase;
    public function test_squad_transitions_and_legacy_loading(): void
    {
        $coach=User::factory()->create();$team=Team::factory()->create(['coach_id'=>$coach->id]);$player=User::factory()->create();$team->players()->attach($player);
        $this->withSession(['user_id'=>$coach->id]);
        $position=['player_id'=>$player->id,'x'=>24.5,'y'=>63,'role'=>'DEF','status'=>'starter'];
        $this->postJson(route('formations.store',$team),['title'=>'Squad','positions'=>[$position]])->assertCreated();
        $formation=Formation::firstOrFail();
        $position['status']='substitute';
        $this->patchJson(route('formations.update',$formation),['title'=>'Squad','positions'=>[$position]])->assertOk();
        $this->assertSame('substitute',$formation->fresh()->positions[0]['status']);
        $this->getJson(route('formations.show',$formation))->assertJsonPath('positions.0.x',24.5);
        $position['status']='starter';
        $this->patchJson(route('formations.update',$formation),['title'=>'Squad','positions'=>[$position]])->assertOk();
        $this->assertSame('starter',$formation->fresh()->positions[0]['status']);
        unset($position['status']);$formation->update(['positions'=>[$position]]);
        $this->get(route('formations.edit',$formation))->assertOk()->assertSee('Available Players');
        $this->withSession(['user_id'=>$player->id])->patchJson(route('formations.update',$formation),['title'=>'No','positions'=>[$position]])->assertForbidden();
    }
    public function test_starter_limit_duplicates_and_membership(): void
    {
        $coach=User::factory()->create();$team=Team::factory()->create(['coach_id'=>$coach->id]);$players=User::factory()->count(12)->create();$team->players()->attach($players->modelKeys());
        $positions=$players->map(fn($p)=>['player_id'=>$p->id,'x'=>50,'y'=>50,'role'=>'MID','status'=>'starter'])->all();
        $this->withSession(['user_id'=>$coach->id]);
        $this->postJson(route('formations.store',$team),['title'=>'Squad','positions'=>$positions])->assertUnprocessable();
        $positions[11]['status']='substitute';
        $this->postJson(route('formations.store',$team),['title'=>'Squad','positions'=>$positions])->assertCreated();
        $positions[11]['player_id']=$positions[0]['player_id'];
        $this->postJson(route('formations.store',$team),['title'=>'Squad','positions'=>$positions])->assertUnprocessable();
        $positions[11]['player_id']=User::factory()->create()->id;
        $this->postJson(route('formations.store',$team),['title'=>'Squad','positions'=>$positions])->assertUnprocessable();
    }
}
