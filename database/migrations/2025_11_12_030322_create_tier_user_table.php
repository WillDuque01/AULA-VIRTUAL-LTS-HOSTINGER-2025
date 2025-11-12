<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->string('source')->default('manual');
            $table->foreignId('assigned_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tier_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_user');
    }
};
