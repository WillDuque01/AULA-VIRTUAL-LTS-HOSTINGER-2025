<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_progress', function (Blueprint $table): void {
            if (! Schema::hasColumn('video_progress', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('watched_seconds');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'experience_points')) {
                $table->unsignedInteger('experience_points')->default(0)->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'current_streak')) {
                $table->unsignedSmallInteger('current_streak')->default(0)->after('experience_points');
            }
            if (! Schema::hasColumn('users', 'last_completion_at')) {
                $table->timestamp('last_completion_at')->nullable()->after('current_streak');
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_progress', function (Blueprint $table): void {
            $table->dropColumn('completed_at');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['experience_points', 'current_streak', 'last_completion_at']);
        });
    }
};


