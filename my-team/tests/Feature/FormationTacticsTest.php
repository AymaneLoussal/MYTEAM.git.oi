<?php
namespace Tests\Feature;
use App\Models\{Formation,Team,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class FormationTacticsTest extends TestCase {
 use RefreshDatabase;
 public function test_all_presets_round_trip_and_reject_duplicate_slots(): void {
  $coach=User::factory()->create();$team=Team::factory()->create(['coach_id'=>$coach->id]);$players=User::factory()->count(11)->create();$team->players()->attach($players->modelKeys());$this->withSession(['user_id'=>$coach->id]);
  foreach(config('tactics.presets') as $preset=>$rows){
   $labels=array_merge(...$rows);$this->assertCount(11,$labels);$this->assertSame('GK',$labels[0]);
   $positions=[];foreach($labels as $i=>$label)$positions[]=['player_id'=>$players[$i]->id,'role'=>config('tactics.roles.'.$label),'position'=>$label,'slot'=>$i,'status'=>'starter','x'=>25.5,'y'=>60];
   $this->postJson(route('formations.store',$team),['title'=>$preset,'preset'=>$preset,'positions'=>$positions])->assertCreated();
   $formation=Formation::latest('id')->first();$this->getJson(route('formations.show',$formation))->assertJsonPath('preset',$preset)->assertJsonPath('positions.10.position',$labels[10])->assertJsonPath('positions.0.x',25.5);
   [$positions[0]['slot'],$positions[1]['slot']]=[$positions[1]['slot'],$positions[0]['slot']];
   $this->patchJson(route('formations.update',$formation),['title'=>$preset,'preset'=>$preset,'positions'=>$positions])->assertOk();
   $positions[1]['slot']=$positions[0]['slot'];$this->patchJson(route('formations.update',$formation),['title'=>$preset,'preset'=>$preset,'positions'=>$positions])->assertUnprocessable();
  }
 }
}
