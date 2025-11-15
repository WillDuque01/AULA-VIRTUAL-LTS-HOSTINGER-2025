<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('instructions')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('max_points')->default(100);
            $table->json('rubric')->nullable();
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('body')->nullable();
            $table->string('attachment_url')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('max_points')->nullable();
            $table->text('feedback')->nullable();
            $table->json('rubric_scores')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->index(['assignment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};


