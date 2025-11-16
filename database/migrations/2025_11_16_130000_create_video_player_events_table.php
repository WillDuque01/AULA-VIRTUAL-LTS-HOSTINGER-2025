<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_player_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('lesson_id')->nullable()->constrained();
            $table->foreignId('course_id')->nullable()->constrained();
            $table->string('event', 50);
            $table->string('provider', 20)->nullable();
            $table->unsignedInteger('playback_seconds')->default(0);
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->unsignedInteger('video_duration')->nullable();
            $table->decimal('playback_rate', 4, 2)->default(1.00);
            $table->string('context_tag', 50)->default('player');
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['lesson_id', 'event']);
            $table->index(['user_id', 'event']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_player_events');
    }
};

