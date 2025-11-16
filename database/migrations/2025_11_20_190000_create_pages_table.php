<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 32)->default('landing'); // home | landing | custom
            $table->string('locale', 5)->default('es');
            $table->string('status', 32)->default('draft'); // draft | published | archived
            $table->unsignedBigInteger('published_revision_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('page_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->json('layout')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->foreign('published_revision_id')->references('id')->on('page_revisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropForeign(['published_revision_id']);
        });
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
    }
};


