<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tierables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tier_id')->constrained()->cascadeOnDelete();
            $table->morphs('tierable');
            $table->timestamps();

            $table->unique(['tier_id', 'tierable_id', 'tierable_type'], 'tierables_tier_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tierables');
    }
};
