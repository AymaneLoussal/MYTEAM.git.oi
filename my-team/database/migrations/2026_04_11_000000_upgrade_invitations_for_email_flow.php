<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('email')->nullable()->after('team_id');
            $table->foreignId('invited_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('token', 64)->nullable()->after('invited_by')->unique();
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('responded_at')->nullable()->after('expires_at');
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by');
            $table->dropColumn(['email', 'token', 'expires_at', 'responded_at']);
        });
    }
};
