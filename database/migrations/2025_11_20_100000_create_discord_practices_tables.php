<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_practices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('cohort_label')->nullable();
            $table->foreignId('practice_package_id')->nullable()->constrained('practice_packages')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('cohort'); // cohort | global
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->unsignedInteger('capacity')->default(12);
            $table->string('discord_channel_url')->nullable();
            $table->string('meeting_token')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, cancelled, completed
            $table->boolean('requires_package')->default(false);
            $table->timestamps();
        });

        Schema::create('discord_practice_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('discord_practice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
             $table->foreignId('practice_package_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['discord_practice_id', 'user_id']);
        });

        Schema::create('discord_practice_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discord_practice_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cohort_label')->nullable();
            $table->string('status')->default('pending'); // pending, merged, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_practice_requests');
        Schema::dropIfExists('discord_practice_reservations');
        Schema::dropIfExists('discord_practices');
    }
};


