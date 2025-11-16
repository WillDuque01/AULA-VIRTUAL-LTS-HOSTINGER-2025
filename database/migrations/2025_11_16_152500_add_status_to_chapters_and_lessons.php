<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            if (! Schema::hasColumn('chapters', 'status')) {
                $table->string('status')->default('published')->after('position');
            }

            if (! Schema::hasColumn('chapters', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'status')) {
                $table->string('status')->default('published')->after('locked');
            }

            if (! Schema::hasColumn('lessons', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            if (Schema::hasColumn('chapters', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('chapters', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('lessons', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

