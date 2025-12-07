<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_practices', function (Blueprint $table): void {
            $table->index('start_at', 'idx_start_at');
        });
    }

    public function down(): void
    {
        Schema::table('discord_practices', function (Blueprint $table): void {
            $table->dropIndex('idx_start_at');
        });
    }
};

