<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50);
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 280)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft'); // draft | published | archived
            $table->string('category')->nullable();
            $table->string('badge')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->decimal('price_amount', 10, 2)->default(0);
            $table->decimal('compare_at_amount', 10, 2)->nullable();
            $table->string('price_currency', 3)->default('USD');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('inventory')->nullable();
            $table->morphs('productable');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


