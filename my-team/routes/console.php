<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:reconcile-groups {--apply : Remove only empty duplicates with identical participant state}', function () {
    $duplicates = \Illuminate\Support\Facades\DB::table('conversations')->where('type', 'team_group')
        ->select('team_id')->groupBy('team_id')->havingRaw('COUNT(*) > 1')->pluck('team_id');
    foreach ($duplicates as $teamId) {
        \Illuminate\Support\Facades\DB::transaction(function () use ($teamId) {
            $groups = \Illuminate\Support\Facades\DB::table('conversations')->where('team_id', $teamId)->where('type', 'team_group')->orderBy('id')->get();
            $canonical = $groups->first();
            $participants = fn ($id) => \Illuminate\Support\Facades\DB::table('conversation_participants')->where('conversation_id', $id)->orderBy('user_id')->get(['user_id', 'last_read_at'])->toArray();
            foreach ($groups->skip(1) as $group) {
                $safe = ! \Illuminate\Support\Facades\DB::table('messages')->where('conversation_id', $group->id)->exists()
                    && $participants($canonical->id) == $participants($group->id)
                    && $group->subject === $canonical->subject && $group->created_by === $canonical->created_by;
                if (! $safe) {
                    $this->warn("Team {$teamId}: conversation {$group->id} conflicts with {$canonical->id}; manual review required.");
                } elseif (! $this->option('apply')) {
                    $this->line("Team {$teamId}: empty duplicate {$group->id} can be removed with --apply; canonical {$canonical->id}.");
                } else {
                    \Illuminate\Support\Facades\DB::table('conversation_participants')->where('conversation_id', $group->id)->delete();
                    \Illuminate\Support\Facades\DB::table('conversations')->where('id', $group->id)->delete();
                    $this->info("Team {$teamId}: removed empty duplicate {$group->id}; retained {$canonical->id}.");
                }
            }
        });
    }
    if ($duplicates->isEmpty()) $this->info('No duplicate team group conversations.');
})->purpose('Inspect or safely reconcile empty duplicate team group conversations');
