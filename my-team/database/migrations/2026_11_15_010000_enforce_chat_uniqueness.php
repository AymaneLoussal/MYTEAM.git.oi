<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();
        if (! in_array($driver, ['sqlite', 'pgsql'], true)) throw new RuntimeException('Chat integrity migration supports SQLite and PostgreSQL.');
        $duplicates = DB::table('conversations')->where('type', 'team_group')->select('team_id')->groupBy('team_id')->havingRaw('COUNT(*) > 1')->pluck('team_id');
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Duplicate team groups require reconciliation: teams '.$duplicates->join(', ').'. Run chat:reconcile-groups.');
        }
        $privateKeys = [];
        foreach (DB::table('conversations')->where('type', 'coach_player_private')->orderBy('id')->get() as $chat) {
            $coach = DB::table('teams')->where('id', $chat->team_id)->value('coach_id');
            $ids = DB::table('conversation_participants')->where('conversation_id', $chat->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
            $players = array_values(array_filter($ids, fn ($id) => $id !== (int) $coach));
            if (! $coach || count($ids) !== 2 || ! in_array((int) $coach, $ids, true) || count($players) !== 1) {
                throw new RuntimeException('Ambiguous private conversation '.$chat->id.' requires manual review.');
            }
            $key = $chat->team_id.':'.$players[0];
            if (isset($privateKeys[$key])) throw new RuntimeException('Duplicate private conversations '.$privateKeys[$key]['id'].' and '.$chat->id.' require manual review.');
            $privateKeys[$key] = ['id' => $chat->id, 'player' => $players[0]];
        }
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('private_player_id')->nullable()->constrained('users');
        });
        foreach ($privateKeys as $key) DB::table('conversations')->where('id', $key['id'])->update(['private_player_id' => $key['player']]);
        DB::statement("CREATE UNIQUE INDEX conversations_one_team_group ON conversations(team_id) WHERE type = 'team_group'");
        DB::statement("CREATE UNIQUE INDEX conversations_one_private_pair ON conversations(team_id, private_player_id) WHERE type = 'coach_player_private' AND private_player_id IS NOT NULL");
        if ($driver === 'sqlite') {
            DB::statement("CREATE TRIGGER conversations_private_key_insert BEFORE INSERT ON conversations WHEN (NEW.type = 'coach_player_private' AND NEW.private_player_id IS NULL) OR (NEW.type <> 'coach_player_private' AND NEW.private_player_id IS NOT NULL) BEGIN SELECT RAISE(ABORT, 'invalid private conversation key'); END");
            DB::statement("CREATE TRIGGER conversations_private_key_update BEFORE UPDATE OF type, private_player_id ON conversations WHEN (NEW.type = 'coach_player_private' AND NEW.private_player_id IS NULL) OR (NEW.type <> 'coach_player_private' AND NEW.private_player_id IS NOT NULL) BEGIN SELECT RAISE(ABORT, 'invalid private conversation key'); END");
        } else {
            DB::unprepared("CREATE FUNCTION ftmball_validate_private_conversation_key() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF (NEW.type = 'coach_player_private' AND NEW.private_player_id IS NULL) OR (NEW.type <> 'coach_player_private' AND NEW.private_player_id IS NOT NULL) THEN RAISE EXCEPTION 'invalid private conversation key'; END IF; RETURN NEW; END; $$");
            DB::statement('CREATE TRIGGER conversations_private_key_insert BEFORE INSERT ON conversations FOR EACH ROW EXECUTE FUNCTION ftmball_validate_private_conversation_key()');
            DB::statement('CREATE TRIGGER conversations_private_key_update BEFORE UPDATE OF type, private_player_id ON conversations FOR EACH ROW EXECUTE FUNCTION ftmball_validate_private_conversation_key()');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') DB::statement('DROP FUNCTION IF EXISTS ftmball_validate_private_conversation_key() CASCADE');
        else { DB::statement('DROP TRIGGER IF EXISTS conversations_private_key_insert'); DB::statement('DROP TRIGGER IF EXISTS conversations_private_key_update'); }
        DB::statement('DROP INDEX IF EXISTS conversations_one_private_pair');
        DB::statement('DROP INDEX IF EXISTS conversations_one_team_group');
        Schema::table('conversations', fn (Blueprint $table) => $table->dropConstrainedForeignId('private_player_id'));
    }
};
