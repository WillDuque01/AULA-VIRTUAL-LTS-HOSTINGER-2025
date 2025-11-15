<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->timestamp('approved_at')->nullable()->after('graded_at');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->dropColumn('approved_at');
        });
    }
};


