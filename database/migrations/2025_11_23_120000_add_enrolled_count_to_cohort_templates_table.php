<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cohort_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('cohort_templates', 'enrolled_count')) {
                $table->unsignedInteger('enrolled_count')
                    ->default(0)
                    ->after('capacity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cohort_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('cohort_templates', 'enrolled_count')) {
                $table->dropColumn('enrolled_count');
            }
        });
    }
};
