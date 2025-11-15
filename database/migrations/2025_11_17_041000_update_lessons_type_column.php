<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            $table->string('type_new', 32)->default('text');
        });

        DB::table('lessons')->update(['type_new' => DB::raw('type')]);

        Schema::table('lessons', function (Blueprint $table): void {
            $table->dropColumn('type');
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->renameColumn('type_new', 'type');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            $table->enum('type_temp', ['video','audio','pdf','text','iframe','quiz'])->default('text');
        });

        DB::table('lessons')->update(['type_temp' => DB::raw('type')]);

        Schema::table('lessons', function (Blueprint $table): void {
            $table->dropColumn('type');
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->renameColumn('type_temp', 'type');
        });
    }
};


