<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohort_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type', 20)->default('cohort');
            $table->string('cohort_label')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedSmallInteger('capacity')->default(10);
            $table->boolean('requires_package')->default(false);
            $table->foreignId('practice_package_id')->nullable()->constrained()->nullOnDelete();
            $table->json('slots');
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_templates');
    }
};


