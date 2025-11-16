<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_practices', function (Blueprint $table): void {
            $table->timestamp('attendance_synced_at')->nullable()->after('status');
        });

        Schema::table('discord_practice_reservations', function (Blueprint $table): void {
            $table->timestamp('cancelled_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('discord_practices', function (Blueprint $table): void {
            $table->dropColumn('attendance_synced_at');
        });

        Schema::table('discord_practice_reservations', function (Blueprint $table): void {
            $table->dropColumn('cancelled_at');
        });
    }
};


