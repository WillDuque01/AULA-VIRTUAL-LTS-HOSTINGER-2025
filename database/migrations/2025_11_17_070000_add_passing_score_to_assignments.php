<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            $table->unsignedTinyInteger('passing_score')->default(70)->after('max_points');
            $table->boolean('requires_approval')->default(true)->after('passing_score');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropColumn(['passing_score', 'requires_approval']);
        });
    }
};


