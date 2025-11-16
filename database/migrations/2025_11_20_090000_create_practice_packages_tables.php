<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sessions_count')->default(3);
            $table->decimal('price_amount', 10, 2)->default(0);
            $table->string('price_currency', 3)->default('USD');
            $table->boolean('is_global')->default(false);
            $table->string('visibility')->default('private'); // private | public
            $table->string('delivery_platform')->default('discord');
            $table->string('delivery_url')->nullable();
            $table->string('status')->default('draft'); // draft | published | archived
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('practice_package_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('practice_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | paid | cancelled | completed
            $table->unsignedInteger('sessions_remaining')->default(0);
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['practice_package_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_package_orders');
        Schema::dropIfExists('practice_packages');
    }
};


