<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('formations',fn(Blueprint $table)=>$table->string('preset')->nullable()); }
 public function down(): void { Schema::table('formations',fn(Blueprint $table)=>$table->dropColumn('preset')); }
};
