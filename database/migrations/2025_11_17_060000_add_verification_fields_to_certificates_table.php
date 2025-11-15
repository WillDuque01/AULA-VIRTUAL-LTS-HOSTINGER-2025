<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->unsignedInteger('verified_count')->default(0)->after('metadata');
            $table->timestamp('last_verified_at')->nullable()->after('verified_count');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropColumn(['verified_count', 'last_verified_at']);
        });
    }
};


