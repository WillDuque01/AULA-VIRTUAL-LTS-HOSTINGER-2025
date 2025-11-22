<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teacher_activity_snapshots') || ! Schema::hasTable('practice_packages')) {
            return;
        }

        Schema::table('teacher_activity_snapshots', function (Blueprint $table): void {
            if (! Schema::hasColumn('teacher_activity_snapshots', 'practice_package_id')) {
                return;
            }

            $table->foreign(
                'practice_package_id',
                'teacher_activity_snapshots_practice_package_id_foreign'
            )
                ->references('id')
                ->on('practice_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('teacher_activity_snapshots')) {
            return;
        }

        Schema::table('teacher_activity_snapshots', function (Blueprint $table): void {
            if (Schema::hasColumn('teacher_activity_snapshots', 'practice_package_id')) {
                $table->dropForeign('teacher_activity_snapshots_practice_package_id_foreign');
            }
        });
    }
};


