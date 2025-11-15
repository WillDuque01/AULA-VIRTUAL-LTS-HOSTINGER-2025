<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_progress', function (Blueprint $table): void {
            $table->integer('last_recorded_bucket')->nullable()->after('watched_seconds');
        });

        Schema::create('video_heatmap_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('bucket');
            $table->unsignedBigInteger('reach_count')->default(0);
            $table->timestamps();

            $table->unique(['lesson_id', 'bucket']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_heatmap_segments');

        Schema::table('video_progress', function (Blueprint $table): void {
            $table->dropColumn('last_recorded_bucket');
        });
    }
};

