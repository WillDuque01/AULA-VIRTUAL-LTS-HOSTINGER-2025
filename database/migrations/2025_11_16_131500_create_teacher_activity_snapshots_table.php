<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_activity_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('course_id')->nullable()->constrained();
            $table->foreignId('lesson_id')->nullable()->constrained();
            $table->foreignId('practice_package_id')->nullable()->constrained();
            $table->string('category', 50);
            $table->string('scope', 50)->nullable();
            $table->unsignedInteger('value')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'captured_at']);
            $table->index(['course_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_activity_snapshots');
    }
};

