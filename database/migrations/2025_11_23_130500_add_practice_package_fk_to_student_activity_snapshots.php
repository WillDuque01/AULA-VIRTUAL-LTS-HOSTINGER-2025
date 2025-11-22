<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_activity_snapshots') || ! Schema::hasTable('practice_packages')) {
            return;
        }

        Schema::table('student_activity_snapshots', function (Blueprint $table): void {
            $hasColumn = Schema::hasColumn('student_activity_snapshots', 'practice_package_id');

            if (! $hasColumn) {
                return;
            }

            $table->foreign('practice_package_id', 'student_activity_snapshots_practice_package_id_foreign')
                ->references('id')
                ->on('practice_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_activity_snapshots')) {
            return;
        }

        Schema::table('student_activity_snapshots', function (Blueprint $table): void {
            if (Schema::hasColumn('student_activity_snapshots', 'practice_package_id')) {
                $table->dropForeign('student_activity_snapshots_practice_package_id_foreign');
            }
        });
    }
};


